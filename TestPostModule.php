<?php

declare(strict_types=1);

namespace Fisharebest\Webtrees\Module;

use Fig\Http\Message\RequestMethodInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class TestPostModule
 */
class TestPostModule extends AbstractModule implements ModuleCustomInterface, ModuleMenuInterface, RequestHandlerInterface
{
    use ModuleCustomTrait;
    use ModuleMenuTrait;
    use ViewResponseTrait;

    protected const ROUTE_URL = '/tree/{tree}/testpost';
    protected const ROUTE_URL_DO_POST = '/tree/{tree}/do_post';

    /**
     * Initialization.
     *
     * @return void
     */
    public function boot(): void
    {
        $routeMap = Registry::routeFactory()->routeMap();
        $routeMap->get(static::class, static::ROUTE_URL, $this);
        $routeMap->post(static::class . ':doPost', static::ROUTE_URL_DO_POST, $this);
        $routeMap->allows(RequestMethodInterface::METHOD_POST);
        
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
    }

    /**
     * How should this module be identified in the control panel, etc.?
     *
     * @return string
     */
    public function title(): string
    {
        return I18N::translate('Test');
    }

    /**
     * Where does this module store its resources
     *
     * @return string
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    /**
     * A sentence describing what this module does.
     *
     * @return string
     */
    public function description(): string
    {
        return I18N::translate('A simple test post page.');
    }

    /**
     * The default position for this menu.
     *
     * @return int
     */
    public function defaultMenuOrder(): int
    {
        return 9;
    }

    /**
     * A menu, to be added to the main application menu.
     *
     * @param Tree $tree
     *
     * @return Menu|null
     */
    public function getMenu(Tree $tree): ?Menu
    {
        return new Menu(
            $this->title(),
            route(static::class, ['tree' => $tree->name()]),
            'menu-test',
            ['rel' => 'nofollow']
        );
    }

    /**
     * Handle requests
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $user = Validator::attributes($request)->user();
        
        Auth::checkComponentAccess($this, ModuleCustomInterface::class, $tree, $user);

        // Check if this is a POST request to do_post
        if ($request->getMethod() === 'POST' && strpos($request->getRequestTarget(), 'do_post') !== false) {
            return $this->handleDoPost($request);
        }

        if ($request->getMethod() === 'POST') return(response('works2:'.$request->getRequestTarget()));

        // Default: show the form
        return $this->viewResponse($this->name() . '::form', [
            'title' => $this->title(),
            'tree'  => $tree,
            'module' => $this
        ]);
    }

    /**
     * Handle the post submission
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    protected function handleDoPost(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $params = (array) $request->getParsedBody();
        $content = $params['content'] ?? '';

        return $this->viewResponse($this->name() . '::result', [
            'title' => I18N::translate('Post Result'),
            'tree'  => $tree,
            'content' => $content,
            'module' => $this
        ]);
    }
}