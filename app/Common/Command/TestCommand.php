<?php

declare(strict_types=1);

namespace App\Common\Command;

use App\Admin\Service\AdminService;
use App\Admin\Service\SysRoleService;
use App\Common\Utils\UploadFile;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Validation\Rules\File;
use Psr\Container\ContainerInterface;
use function Hyperf\Support\make;

#[Command]
class TestCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('test');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $this->line('Hello Hyperf!', 'info');


        $file_path = BASE_PATH. '/aaaaa.jpeg';
        $file = new UploadedFile($file_path,filesize($file_path),0,$file_path);
        $upload = UploadFile::upload($file);
        var_dump($upload);die;


        $service = make(SysRoleService::class);
        $info = $service->getList(['limit' => 1,'page' => 4]);
        var_dump($info);



    }
}
