<?php

namespace App\Services\AWS;

use Aws\Ecs\EcsClient;
use Aws\Result;

class FargateService
{
    protected ?EcsClient $ecs;

    /**
     * @param string        $cluster
     * @param string        $taskDefinitionArn
     * @param string        $containerName
     * @param array<string> $command
     *
     * @return Result<mixed>
     */
    public function runTask(string $cluster, string $taskDefinitionArn, string $containerName, array $command = []): Result
    {
        $taskParams = [
            'cluster' => $cluster,
            'launchType' => 'FARGATE',
            'taskDefinition' => $taskDefinitionArn,
            'count' => 1,
            'networkConfiguration' => [
                'awsvpcConfiguration' => [
                    'subnets' => config('services.aws.subnets'),
                    'securityGroups' => [config('services.aws.libryo_app_security_group')],
                    'assignPublicIp' => 'ENABLED',
                ],
            ],
        ];

        if (!empty($command)) {
            $taskParams['overrides'] = [
                'containerOverrides' => [
                    [
                        'name' => $containerName,
                        'command' => $command,
                    ],
                ],
            ];
        }

        return $this->getEcsClient()->runTask($taskParams);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return EcsClient
     */
    public function getEcsClient(): EcsClient
    {
        if (isset($this->ecs)) {
            return $this->ecs;
        }
        $this->ecs = new EcsClient([
            'version' => 'latest',
            'region' => config('services.aws.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        return $this->ecs;
    }
}
