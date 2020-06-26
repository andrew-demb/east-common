<?php

use Behat\Behat\Context\Context;
use DI\Container;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\ObjectManager;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Loader\TypeLoader;
use Teknoo\East\Website\Writer\ContentWriter;
use Teknoo\East\Website\Writer\ItemWriter;
use Teknoo\East\Website\Writer\TypeWriter;
use Teknoo\East\Website\EndPoint\MediaEndPointTrait;
use Teknoo\East\Website\EndPoint\ContentEndPointTrait;
use Teknoo\East\Website\EndPoint\StaticEndPointTrait;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\Block;
use Teknoo\East\Website\Service\FindSlugService;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminNewEndPoint;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEditEndPoint;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminDeleteEndPoint;
use Teknoo\East\WebsiteBundle\Form\Type\TypeType;
use Teknoo\East\Website\Doctrine\Form\Type\ContentType;
use Teknoo\East\Website\Doctrine\Form\Type\ItemType;
use Teknoo\East\Website\Doctrine\Object\Content as ContentDoctrine;
use Teknoo\East\Website\Doctrine\Object\Item as ItemDoctrine;
/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private ?Container $container = null;

    private ?RouterInterface $router = null;

    private ?ClientInterface $client = null;

    private ?ObjectRepository $objectRepository = null;

    private ?MediaLoader $mediaLoader = null;

    private ?ContentLoader $contentLoader = null;

    private ?ItemLoader $itemLoader = null;

    private ?TypeLoader $typeLoader = null;

    private ?ContentWriter $contentWriter = null;

    private ?ItemWriter $itemWriter = null;

    private ?TypeWriter $typeWriter = null;

    /**
     * @var MediaEndPointTrait|EndPointInterface
     */
    private ?EndPointInterface $mediaEndPoint = null;

    /**
     * @var ContentEndPointTrait|EndPointInterface
     */
    private ?EndPointInterface $contentEndPoint = null;

    /**
     * @var StaticEndPointTrait|EndPointInterface
     */
    private ?EndPointInterface $staticEndPoint = null;

    private ?AdminNewEndPoint $newContentEndPoint = null;

    private ?AdminEditEndPoint $updateContentEndPoint = null;

    private ?AdminDeleteEndPoint $deleteContentEndPoint = null;

    private ?AdminNewEndPoint $newItemEndPoint = null;

    private ?AdminEditEndPoint $updateItemEndPoint = null;

    private ?AdminDeleteEndPoint $deleteItemEndPoint = null;

    private ?AdminNewEndPoint $newTypeEndPoint = null;

    private ?AdminEditEndPoint $updateTypeEndPoint = null;

    private ?AdminDeleteEndPoint $deleteTypeEndPoint = null;

    private ?Type $type = null;

    private ?EngineInterface $templating = null;

    public ?string $templateToCall = null;

    public ?string $templateContent = null;

    public ?ResponseInterface $response = null;

    public ?\Throwable $error = null;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given I have DI initialized
     */
    public function iHaveDiInitialized()
    {
        $containerDefinition = new \DI\ContainerBuilder();
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/vendor/teknoo/east-foundation/src/universal/di.php'
        );
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)) . '/src/di.php'
        );
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/infrastructures/doctrine/di.php'
        );
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/infrastructures/di.php'
        );

        $this->container = $containerDefinition->build();

        $this->container->set(ObjectManager::class, $this->buildObjectManager());
    }

    /**
     * @Given I have DI With Symfony initialized
     */
    public function iHaveDiWithSymfonyInitialized()
    {
        $containerDefinition = new \DI\ContainerBuilder();
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/vendor/teknoo/east-foundation/src/universal/di.php'
        );
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)) . '/src/di.php'
        );
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/infrastructures/doctrine/di.php'
        );
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/infrastructures/symfony/Resources/config/di.php'
        );
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/infrastructures/di.php'
        );

        $this->container = $containerDefinition->build();

        $this->container->set(ObjectManager::class, $this->buildObjectManager());
    }

    public function buildObjectManager(): ObjectManager
    {
        return new class($this) implements ObjectManager {
            private $featureContext;

            /**
             *  constructor.
             * @param FeatureContext $featureContext
             */
            public function __construct(FeatureContext $featureContext)
            {
                $this->featureContext = $featureContext;
            }

            public function find($className, $id)
            {
            }

            public function persist($object)
            {
            }

            public function remove($object)
            {
            }

            public function merge($object)
            {
            }

            public function clear($objectName = null)
            {
            }

            public function detach($object)
            {
            }

            public function refresh($object)
            {
            }

            public function flush()
            {
            }

            public function getRepository($className)
            {
                return $this->featureContext->buildObjectRepository($className);
            }

            public function getClassMetadata($className)
            {
            }

            public function getMetadataFactory()
            {
            }

            public function initializeObject($obj)
            {
            }

            public function contains($object)
            {
            }
        };
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    public function buildObjectRepository(string $className): ObjectRepository
    {
        $this->objectRepository = new class($className) implements ObjectRepository {
            /**
             * @var string
             */
            private $className;

            /**
             * @var object
             */
            private $object;

            /**
             * @var array
             */
            private $criteria;

            /**
             *  constructor.
             * @param string $className
             */
            public function __construct(string $className)
            {
                $this->className = $className;
            }

            /**
             * @param array $criteria
             * @param object $object
             * @return $this
             */
            public function setObject(array $criteria, $object): self
            {
                $this->criteria = $criteria;
                $this->object = $object;

                return $this;
            }

            public function find($id)
            {
                // TODO: Implement find() method.
            }

            public function findAll()
            {
                // TODO: Implement findAll() method.
            }

            public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
            {
            }

            public function findOneBy(array $criteria)
            {
                if (\array_key_exists('deletedAt', $criteria)) {
                    unset($criteria['deletedAt']);
                }
                
                if (isset($criteria['slug']) && 'page-with-error' == $criteria['slug']) {
                    throw new \Exception('Error');
                }

                if ($this->criteria == $criteria) {
                    return $this->object;
                }

                return null;
            }

            public function getClassName()
            {
                return $this->className;
            }
        };

        return $this->objectRepository;
    }

    /**
     * @Given a Media Loader
     */
    public function aMediaLoader()
    {
        $this->mediaLoader = $this->container->get(MediaLoader::class);
    }
    
    /**
     * @Given a Item Loader
     */
    public function aItemLoader()
    {
        $this->itemLoader = $this->container->get(ItemLoader::class);
    }

    /**
     * @Given a Item Writer
     */
    public function aItemWriter()
    {
        $this->itemWriter = $this->container->get(ItemWriter::class);
    }

    /**
     * @Given a Content Writer
     */
    public function aContentWriter()
    {
        $this->contentWriter = $this->container->get(ContentWriter::class);
    }

    /**
     * @Given a Type Loader
     */
    public function aTypeLoader()
    {
        $this->typeLoader = $this->container->get(TypeLoader::class);
    }

    /**
     * @Given a Type Writer
     */
    public function aTypeWriter()
    {
        $this->typeWriter = $this->container->get(TypeWriter::class);
    }

    /**
     * @Given an available image called :name
     */
    public function anAvailableImageCalled($name)
    {
        $media = new class extends Media {
            /**
             * @inheritDoc
             */
            public function getResource()
            {
                $hf = \fopen('php://memory', 'rw');
                fwrite($hf, 'fooBar');
                fseek($hf, 0);

                return $hf;
            }
        };

        $this->objectRepository->setObject(
            ['id' => $name],
            $media->setId($name)
                ->setName($name)
        );
    }

    /**
     * @Given a Endpoint able to serve resource from database.
     */
    public function aEndpointAbleToServeResourceFromDatabase()
    {
        $this->mediaEndPoint = new class(
            $this->mediaLoader,
            $this->container->get(StreamFactoryInterface::class)
        ) implements EndPointInterface {
            use EastEndPointTrait;
            use MediaEndPointTrait;

            protected function getStream(Media $media): StreamInterface
            {
                $hf = fopen('php://memory', 'rw+');
                fwrite($hf, 'fooBar');
                fseek($hf, 0);

                return $this->streamFactory->createStreamFromResource($hf);
            }
        };

        $this->mediaEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
    }

    /**
     * @Given I register a router
     */
    public function iRegisterARouter()
    {
        $this->router = new class implements RouterInterface {
            private $routes = [];
            private $params = [];

            public function registerRoute(string $route, callable $controller, array $params = []): self
            {
                $this->routes[$route] = $controller;
                $this->params[$route] = $params;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function execute(
                ClientInterface $client,
                ServerRequestInterface $request,
                ManagerInterface $manager
            ): MiddlewareInterface {
                $path = $request->getUri()->getPath();

                foreach ($this->routes as $route => $endpoint) {
                    $values = [];
                    if (\preg_match($route, $path, $values)) {
                        $result = new Result($endpoint);
                        $request = $request->withAttribute(RouterInterface::ROUTER_RESULT_KEY, $result);

                        foreach ($values as $key=>$value) {
                            if (!\is_numeric($key)) {
                                $request = $request->withAttribute($key, $value);
                            }
                        }

                        foreach ($this->params[$route] as $key=>$value) {
                            $request = $request->withAttribute($key, $value);
                        }

                        $manager->updateWorkPlan([\Teknoo\East\Foundation\Router\ResultInterface::class => $result]);

                        $manager->continueExecution($client, $request);

                        break;
                    }
                }

                return $this;
            }
        };

        $this->container->set(RouterInterface::class, $this->router);
    }

    /**
     * @Given The router can process the request :url to controller :controller
     */
    public function theRouterCanProcessTheRequestToController($url, $controller)
    {
        switch ($controller) {
            case 'contentEndPoint':
                $controller = $this->contentEndPoint;
                $this->router->registerRoute($url, $controller);
                break;
            case 'staticEndPoint':
                $controller = $this->staticEndPoint;
                $this->router->registerRoute($url, $controller, ['template' => 'Acme:MyBundle:template.html.twig']);
                break;
            case 'mediaEndPoint':
                $controller = $this->mediaEndPoint;
                $this->router->registerRoute($url, $controller);
                break;
        }
    }

    /**
     * @When The server will receive the request :url
     */
    public function theServerWillReceiveTheRequest($url)
    {
        $manager = new Manager($this->container->get(RecipeInterface::class));

        $this->response = null;
        $this->error = null;

        $this->client = new class($this) implements ClientInterface {
            /**
             * @var FeatureContext
             */
            private $context;

            /**
             *  constructor.
             * @param FeatureContext $context
             */
            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            /**
             * @inheritDoc
             */
            public function updateResponse(callable $modifier): ClientInterface
            {
                $modifier($this, $this->context->response);

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function acceptResponse(ResponseInterface $response): ClientInterface
            {
                $this->context->response = $response;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function sendResponse(ResponseInterface $response = null, bool $silently = false): ClientInterface
            {
                if (!empty($response)) {
                    $this->context->response = $response;
                }

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function errorInRequest(\Throwable $throwable): ClientInterface
            {
                $this->context->error = $throwable;

                return $this;
            }
        };

        $request = new ServerRequest();
        $request = $request->withMethod('GET');
        $request = $request->withUri(new Uri($url));
        $query = [];
        \parse_str($request->getUri()->getQuery(), $query);
        $request = $request->withQueryParams($query);

        $manager->receiveRequest(
            $this->client,
            $request
        );
    }

    /**
     * @Then The client must accept a response
     */
    public function theClientMustAcceptAResponse()
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertNull($this->error);
    }

    /**
     * @Then I should get :body
     */
    public function iShouldGet($body)
    {
        Assert::assertEquals($body, (string) $this->response->getBody());
    }

    /**
     * @Then The client must accept an error
     */
    public function theClientMustAcceptAnError()
    {
        Assert::assertNull($this->response);
        Assert::assertInstanceOf(\Throwable::class, $this->error);
    }

    /**
     * @Given a Content Loader
     */
    public function aContentLoader()
    {
        $this->contentLoader = new ContentLoader(
            $this->container->get(ContentRepositoryInterface::class)
        );
    }

    /**
     * @Given a type of page, called :name with :blockNumber blocks :blocks and template :template with :config
     */
    public function aTypeOfPageCalledWithBlocksAndTemplateWith($name, $blockNumber, $blocks, $template, $config)
    {
        $this->type = new Type();
        $this->type->setName($name);
        $blocksList = [];
        foreach (\explode(',', $blocks) as $blockName) {
            $blocksList[] = new Block($blockName, 'text');
        }
        $this->type->setBlocks($blocksList);
        $this->type->setTemplate($template);
        $this->templateToCall = $template;
        $this->templateContent = $config;
    }

    /**
     * @Given an available page with the slug :slug of type :type
     */
    public function anAvailablePageWithTheSlugOfType($slug, $type)
    {
        $this->objectRepository->setObject(
            ['slug' => $slug],
            (new Content())->setSlug($type)
                ->setType($this->type)
                ->setParts(['block1' => 'hello', 'block2' => 'world'])
                ->setPublishedAt(new \DateTime(2017-11-25))
        );
    }

    /**
     * @Given a Endpoint able to render and serve page.
     */
    public function aEndpointAbleToRenderAndServePage()
    {
        $this->contentEndPoint = new class($this->contentLoader, '404-error') implements EndPointInterface {
            use EastEndPointTrait;
            use ContentEndPointTrait;
        };

        $this->contentEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->contentEndPoint->setStreamFactory($this->container->get(StreamFactoryInterface::class));
    }

    /**
     * @Given a Endpoint able to render form and create a type
     */
    public function aEndpointAbleToRenderFormAndCreateAType()
    {
        $this->newTypeEndPoint = new AdminNewEndPoint();
        $this->newTypeEndPoint->setFormFactory($this->container->get(FormFactory::class));
        $this->newTypeEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->newTypeEndPoint->setStreamFactory($this->container->get(StreamFactoryInterface::class));
        $this->newTypeEndPoint->setTemplating($this->templating);
        $this->newTypeEndPoint->setRouter($this->router);
        $this->newTypeEndPoint->setLoader($this->typeLoader);
        $this->newTypeEndPoint->setWriter($this->typeWriter);
        $this->newTypeEndPoint->setFormClass(TypeType::class);
        $this->newTypeEndPoint->setViewPath('file');
        $this->newTypeEndPoint->setObjectClass(Type::class);
    }

    /**
     * @Given a Endpoint able to render form and create a content
     */
    public function aEndpointAbleToRenderFormAndCreateAContent()
    {
        $this->newContentEndPoint = new AdminNewEndPoint();
        $this->newContentEndPoint->setFormFactory($this->container->get(FormFactory::class));
        $this->newContentEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->newContentEndPoint->setStreamFactory($this->container->get(StreamFactoryInterface::class));
        $this->newContentEndPoint->setTemplating($this->templating);
        $this->newContentEndPoint->setRouter($this->router);
        $this->newContentEndPoint->setStreamFactory($this->container->get(FindSlugService::class), 'slug');
        $this->newContentEndPoint->setLoader($this->contentLoader);
        $this->newContentEndPoint->setWriter($this->contentWriter);
        $this->newContentEndPoint->setFormClass(ContentType::class);
        $this->newContentEndPoint->setViewPath('file');
        $this->newContentEndPoint->setObjectClass(ContentDoctrine::class);
    }

    /**
     * @Given a Endpoint able to render form and create a item
     */
    public function aEndpointAbleToRenderFormAndCreateAItem()
    {
        $this->newItemEndPoint = new AdminNewEndPoint();
        $this->newItemEndPoint->setFormFactory($this->container->get(FormFactory::class));
        $this->newItemEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->newItemEndPoint->setStreamFactory($this->container->get(StreamFactoryInterface::class));
        $this->newItemEndPoint->setTemplating($this->templating);
        $this->newItemEndPoint->setRouter($this->router);
        $this->newItemEndPoint->setStreamFactory($this->container->get(FindSlugService::class), 'slug');
        $this->newItemEndPoint->setLoader($this->itemLoader);
        $this->newItemEndPoint->setWriter($this->itemWriter);
        $this->newItemEndPoint->setFormClass(ItemType::class);
        $this->newItemEndPoint->setViewPath('file');
        $this->newItemEndPoint->setObjectClass(ItemDoctrine::class);
    }

    /**
     * @Given a Endpoint able to render form and delete a type
     */
    public function aEndpointAbleToRenderFormAndDeleteAType()
    {
        $this->deleteTypeEndPoint = new AdminDeleteEndPoint();
        $this->deleteTypeEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->deleteTypeEndPoint->setRouter($this->router);
        $this->deleteTypeEndPoint->setDeletingService($this->container->get('teknoo.east.website.deleting.item'));
        $this->deleteTypeEndPoint->setLoader($this->typeLoader);
    }

    /**
     * @Given a Endpoint able to render form and delete a content
     */
    public function aEndpointAbleToRenderFormAndDeleteAContent()
    {
        $this->deleteContentEndPoint = new AdminDeleteEndPoint();
        $this->deleteContentEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->deleteContentEndPoint->setRouter($this->router);
        $this->deleteContentEndPoint->setDeletingService($this->container->get('teknoo.east.website.deleting.content'));
        $this->deleteContentEndPoint->setLoader($this->contentLoader);
    }

    /**
     * @Given a Endpoint able to render form and delete a item
     */
    public function aEndpointAbleToRenderFormAndDeleteAItem()
    {
        $this->deleteItemEndPoint = new AdminDeleteEndPoint();
        $this->deleteItemEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->deleteItemEndPoint->setRouter($this->router);
        $this->deleteItemEndPoint->setDeletingService($this->container->get('teknoo.east.website.deleting.content'));
        $this->deleteItemEndPoint->setLoader($this->itemLoader);
    }

    /**
     * @Given a Endpoint able to render form and update a type
     */
    public function aEndpointAbleToRenderFormAndUpdateAType()
    {
        $this->updateTypeEndPoint = new AdminEditEndPoint();
        $this->updateTypeEndPoint->setFormFactory($this->container->get(FormFactory::class));
        $this->updateTypeEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->updateTypeEndPoint->setStreamFactory($this->container->get(StreamFactoryInterface::class));
        $this->updateTypeEndPoint->setTemplating($this->templating);
        $this->updateTypeEndPoint->setLoader($this->typeLoader);
        $this->updateTypeEndPoint->setWriter($this->typeWriter);
        $this->updateTypeEndPoint->setFormClass(TypeType::class);
        $this->updateTypeEndPoint->setViewPath('file');
    }

    /**
     * @Given a Endpoint able to render form and update a content
     */
    public function aEndpointAbleToRenderFormAndUpdateAContent()
    {
        $this->updateContentEndPoint = new AdminEditEndPoint();
        $this->newContentEndPoint->setFormFactory($this->container->get(FormFactory::class));
        $this->newContentEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->newContentEndPoint->setStreamFactory($this->container->get(StreamFactoryInterface::class));
        $this->newContentEndPoint->setTemplating($this->templating);
        $this->newContentEndPoint->setStreamFactory($this->container->get(FindSlugService::class), 'slug');
        $this->newContentEndPoint->setLoader($this->contentLoader);
        $this->newContentEndPoint->setWriter($this->contentWriter);
        $this->newContentEndPoint->setFormClass(ContentType::class);
        $this->newContentEndPoint->setViewPath('file');
        $this->newContentEndPoint->setObjectClass(ContentDoctrine::class);
    }

    /**
     * @Given a Endpoint able to render form and update a item
     */
    public function aEndpointAbleToRenderFormAndUpdateAItem()
    {
        $this->updateItemEndPoint = new AdminEditEndPoint();
        $this->updateItemEndPoint->setFormFactory($this->container->get(FormFactory::class));
        $this->updateItemEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->updateItemEndPoint->setStreamFactory($this->container->get(StreamFactoryInterface::class));
        $this->updateItemEndPoint->setTemplating($this->templating);
        $this->updateItemEndPoint->setStreamFactory($this->container->get(FindSlugService::class), 'slug');
        $this->updateItemEndPoint->setLoader($this->itemLoader);
        $this->updateItemEndPoint->setWriter($this->itemWriter);
        $this->updateItemEndPoint->setFormClass(ItemType::class);
        $this->updateItemEndPoint->setViewPath('file');
    }

    /**
     * @Given a templating engine
     */
    public function aTemplatingEngine()
    {
        $this->templating = new class($this) implements EngineInterface {
            private $context;

            /**
             * @param FeatureContext $context
             */
            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            public function render($name, array $parameters = array())
            {
                if ('404-error' === $name) {
                    return 'Error 404';
                }
                
                Assert::assertEquals($this->context->templateToCall, $name);

                $keys = [];
                $values = [];
                if (isset($parameters['content']) && $parameters['content'] instanceof Content) {
                    foreach ($parameters['content']->getParts() as $key=>$value) {
                        $keys[] = '{'.$key.'}';
                        $values[] = $value;
                    }
                }

                return \str_replace($keys, $values, $this->context->templateContent);
            }

            public function exists($name)
            {
            }

            public function supports($name)
            {
            }
        };

        if ($this->staticEndPoint instanceof EndPointInterface) {
            $this->staticEndPoint->setTemplating($this->templating);
        }

        if ($this->contentEndPoint instanceof EndPointInterface) {
            $this->contentEndPoint->setTemplating($this->templating);
        }
    }

    /**
     * @Given a template :template with :content
     */
    public function aTemplateWith($template, $content)
    {
        $this->templateToCall = $template;
        $this->templateContent = $content;
    }

    /**
     * @Given a Endpoint able to render and serve this template.
     */
    public function aEndpointAbleToRenderAndServeThisTemplate()
    {
        $this->staticEndPoint = new class implements EndPointInterface {
            use EastEndPointTrait;
            use StaticEndPointTrait;
        };

        $this->staticEndPoint->setResponseFactory($this->container->get(ResponseFactoryInterface::class));
        $this->staticEndPoint->setStreamFactory($this->container->get(StreamFactoryInterface::class));
    }

    /**
     * @When The server will receive the POST request :url with :body
     */
    public function theServerWillReceiveThePostRequestWith($url, $body)
    {
        $manager = new Manager($this->container->get(RecipeInterface::class));

        $this->response = null;
        $this->error = null;

        $this->client = new class($this) implements ClientInterface {
            /**
             * @var FeatureContext
             */
            private $context;

            /**
             *  constructor.
             * @param FeatureContext $context
             */
            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            /**
             * @inheritDoc
             */
            public function updateResponse(callable $modifier): ClientInterface
            {
                $modifier($this, $this->context->response);

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function acceptResponse(ResponseInterface $response): ClientInterface
            {
                $this->context->response = $response;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function sendResponse(ResponseInterface $response = null, bool $silently = false): ClientInterface
            {
                if (!empty($response)) {
                    $this->context->response = $response;
                }

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function errorInRequest(\Throwable $throwable): ClientInterface
            {
                $this->context->error = $throwable;

                return $this;
            }
        };

        $request = new ServerRequest();
        $request = $request->withMethod('POST');
        $request = $request->withUri(new Uri($url));
        $query = [];
        \parse_str($request->getUri()->getQuery(), $query);
        $request = $request->withQueryParams($query);
        $parsedBody = [];
        \parse_str($request->getUri()->getQuery(), $parsedBody);
        $request = $request->withParsedBody($parsedBody);

        $manager->receiveRequest(
            $this->client,
            $request
        );
    }

    /**
     * @Then I should get in the form :body
     */
    public function iShouldGetInTheForm($body)
    {
        $expectedBody = [];
        \parse_str($body, $expectedBody);

        $actualBody = [];
        \parse_str((string) $this->response->getBody(), $actualBody);

        Assert::assertEquals($expectedBody, $actualBody);
    }

    /**
     * @When The server will receive the DELETE request :url
     */
    public function theServerWillReceiveTheDeleteRequest($url)
    {
        $manager = new Manager($this->container->get(RecipeInterface::class));

        $this->response = null;
        $this->error = null;

        $this->client = new class($this) implements ClientInterface {
            /**
             * @var FeatureContext
             */
            private $context;

            /**
             *  constructor.
             * @param FeatureContext $context
             */
            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            /**
             * @inheritDoc
             */
            public function updateResponse(callable $modifier): ClientInterface
            {
                $modifier($this, $this->context->response);

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function acceptResponse(ResponseInterface $response): ClientInterface
            {
                $this->context->response = $response;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function sendResponse(ResponseInterface $response = null, bool $silently = false): ClientInterface
            {
                if (!empty($response)) {
                    $this->context->response = $response;
                }

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function errorInRequest(\Throwable $throwable): ClientInterface
            {
                $this->context->error = $throwable;

                return $this;
            }
        };

        $request = new ServerRequest();
        $request = $request->withMethod('DELETE');
        $request = $request->withUri(new Uri($url));
        $query = [];
        \parse_str($request->getUri()->getQuery(), $query);
        $request = $request->withQueryParams($query);

        $manager->receiveRequest(
            $this->client,
            $request
        );
    }
}
