<?php

namespace App;

use App\Contract\FeeCalculatorInterface;
use App\Contract\FileParserInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerForAutoconfiguration(FileParserInterface::class)
            ->addTag('file.parsing');

        $container->registerForAutoconfiguration(FeeCalculatorInterface::class)
            ->addTag('fee.calculator');
    }
}
