<?php

namespace spec\Ftrrtf\RollbarBundle\Twig;

use Exception;
use Ftrrtf\RollbarBundle\Faker\Application;
use Ftrrtf\RollbarBundle\Helper\UserHelper;
use Ftrrtf\RollbarBundle\Twig\RollbarExtension;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @mixin RollbarExtension
 */
class RollbarExtensionSpec extends ObjectBehavior
{
    function let(UserHelper $helper, ContainerInterface $container)
    {
        $this->beConstructedWith([], [], $helper, $container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ftrrtf\RollbarBundle\Twig\RollbarExtension');
    }

    function it_uses_the_newest_version_of_rollbarjs(UserHelper $helper, Application $app, ContainerInterface $container)
    {
        $this->beConstructedWith(
            [
                'access_token' => 'access_token',
                'source_map_enabled' => false,
                'allowed_js_hosts' => [],
                'check_ignore_function_provider' => null,
                'rollbarjs_version' => 'v1',
            ],
            [
                'environment' => 'test',
            ],
            $helper,
            $container
        );

        $this->getInitRollbarCode(['app' => $app])->shouldMatch('/v1/');
    }

    function it_allows_to_select_rollbarjs_version(UserHelper $helper, Application $app, ContainerInterface $container)
    {
        $this->beConstructedWith(
            [
                'access_token' => 'access_token',
                'source_map_enabled' => false,
                'allowed_js_hosts' => [],
                'check_ignore_function_provider' => null,
                'rollbarjs_version' => 'v1.7',
            ],
            [
                'environment' => 'test',
            ],
            $helper,
            $container
        );

        $this->getInitRollbarCode(['app' => $app])->shouldMatch('/v1.7/');
    }

    function it_throws_runtime_exception_when_check_ignore_function_service_does_not_exist(
        UserHelper $helper,
        Application $app,
        ContainerInterface $container
    ) {
        $exceptionFromContainer = new Exception();
        $nonExistingServiceId = 'service.which.does.not.exist';
        $expectedException = new RuntimeException('Could not load check_ignore_function_provider service');

        $this->beConstructedWith(
            [
                'access_token' => 'access_token',
                'source_map_enabled' => false,
                'allowed_js_hosts' => [],
                'check_ignore_function_provider' => $nonExistingServiceId,
                'rollbarjs_version' => 'v1.7',
            ],
            [
                'environment' => 'test',
            ],
            $helper,
            $container
        );

        $container->get($nonExistingServiceId)->willThrow($exceptionFromContainer);
        $this->shouldThrow($expectedException)->duringGetInitRollbarCode(['app' => $app]);
    }

    function it_throws_runtime_exception_when_ignore_function_service_does_not_implement_proper_interface(
        UserHelper $helper,
        Application $app,
        ContainerInterface $container
    ) {
        $existingServiceId = 'service.which.does.not.implement.proper.interface';
        $expectedException = new RuntimeException(
            'check_ignore_function_provider service should implement CheckIgnoreFunctionProviderInterface'
        );
        $checkIgnoreServiceProvider = new stdClass();

        $this->beConstructedWith(
            [
                'access_token' => 'access_token',
                'source_map_enabled' => false,
                'allowed_js_hosts' => [],
                'check_ignore_function_provider' => $existingServiceId,
                'rollbarjs_version' => 'v1.7',
            ],
            [
                'environment' => 'test',
            ],
            $helper,
            $container
        );

        $container->get($existingServiceId)->willReturn($checkIgnoreServiceProvider);

        $this->shouldThrow($expectedException)->duringGetInitRollbarCode(['app' => $app]);
    }
}
