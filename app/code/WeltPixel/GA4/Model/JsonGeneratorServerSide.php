<?php

namespace WeltPixel\GA4\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use WeltPixel\GA4\Model\Api\ServerSideTracking;

/**
 * Class \WeltPixel\GA4\Model\JsonGenerator
 */
class JsonGeneratorServerSide extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var ServerSideTracking
     */
    protected $apiServerSideTracking;


    /**
     * @var integer
     */
    protected $fingerprint;

    /**
     * @var string
     */
    protected $accountId;

    /**
     * @var string
     */
    protected $containerId;

    /**
     * @var string
     */
    protected $measurementId;

    /**
     * @var string
     */
    protected $publicId;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $jsonFileName;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ServerSideTracking $apiServerSideTracking
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ServerSideTracking $apiServerSideTracking,
        Filesystem $filesystem
    ) {
        parent::__construct($context, $registry);
        $this->apiServerSideTracking = $apiServerSideTracking;
        $this->filesystem = $filesystem;
        $this->jsonFileName = 'ga4Export' . DIRECTORY_SEPARATOR . 'gtmServerSide.json';
    }

    /**
     * @param $accountId
     * @param $containerId
     * @param $publicId
     * @param $measurementId
     * @return string
     */
    public function generateServerSideJson(
        $accountId,
        $containerId,
        $publicId,
        $measurementId
    ) {
        $this->fingerprint = time();
        $this->accountId = $accountId;
        $this->containerId = $containerId;
        $this->publicId = $publicId;
        $this->measurementId = $measurementId;

        $clients = $this->getClientsForJsonGeneration();
        $variables = $this->getVariablesForJsonGeneration();
        $triggers = $this->getTriggersForJsonGeneration();
        $tags = $this->getTagsForJsonGeneration($triggers);

        $containerVersionOptions = [
            "path" => "accounts/$this->accountId/containers/$this->containerId/versions/0",
            "accountId" => $this->accountId,
            "containerId" => $this->containerId,
            "containerVersionId" => "0",
            "container" => [
                "path" => "accounts/$this->accountId/containers/$this->containerId",
                "accountId" => $this->accountId,
                "containerId" => $this->containerId,
                "name" => "WeltPixel GA4 Server Container",
                "publicId" => $this->publicId,
                "usageContext" => [
                    "SERVER"
                ],
                "fingerprint" => $this->fingerprint,
                "tagManagerUrl" => "https://tagmanager.google.com/#/container/accounts/$this->accountId/containers/$this->containerId/workspaces?apiLink=container",
                "features" => [
                    "supportUserPermissions" => true,
                    "supportEnvironments" => true,
                    "supportWorkspaces" => true,
                    "supportGtagConfigs" => false,
                    "supportBuiltInVariables" => true,
                    "supportClients" => true,
                    "supportFolders" => true,
                    "supportTags" => true,
                    "supportTemplates" => true,
                    "supportTriggers" => true,
                    "supportVariables" => true,
                    "supportVersions" => true,
                    "supportZones" => true,
                    "supportTransformations" => true
                ],
                "tagIds" => [
                    $this->publicId
                ]
            ],
            "builtInVariable" => [
                [
                    "accountId" => $this->accountId,
                    "containerId" => $this->containerId,
                    "type" => "EVENT_NAME",
                    "name" => "Event Name"
                ],
                [
                    "accountId" => $this->accountId,
                    "containerId" => $this->containerId,
                    "type" => "VISITOR_REGION",
                    "name" => "Visitor Region"
                ]
            ],
            "client" => array_values($clients),
            "variable" => array_values($variables),
            "trigger" => array_values($triggers),
            "tag" => array_values($tags),
            "fingerprint" => $this->fingerprint
        ];

        $jsonOptions = [
            "exportFormatVersion" => 2,
            "exportTime" => date("Y-m-d h:i:s"),
            "containerVersion" => $containerVersionOptions,
            "fingerprint" => $this->fingerprint,
            "tagManagerUrl" => "https://tagmanager.google.com/#/versions/accounts/$this->accountId/containers/$this->containerId/versions/0?apiLink=version"
        ];

        $jsonExportDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $jsonExportDir->writeFile($this->jsonFileName, json_encode($jsonOptions, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getGeneratedJsonContent()
    {
        $jsonExportDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        return $jsonExportDir->readFile($this->jsonFileName);
    }

    /**
     * @return array
     */
    protected function getClientsForJsonGeneration()
    {
        $clientsToCreate = $this->apiServerSideTracking->getClientsList($this->publicId);

        $clientId = 1;
        foreach ($clientsToCreate as $clientName => &$clientOptions) {
            if (isset($clientOptions['parameter'])) {
                foreach ($clientOptions['parameter'] as &$paramOptions) {
                    if (isset($paramOptions['type'])) {
                        $paramOptions['type'] = strtoupper($paramOptions['type']);
                    }
                }
            }

            $clientOptions['accountId'] = $this->accountId;
            $clientOptions['containerId'] = $this->containerId;
            $clientOptions['clientId'] = $clientId;
            $clientOptions['fingerprint'] = $this->fingerprint;
            $clientId+=1;
        }

        return $clientsToCreate;
    }

    /**
     * @return array
     */
    protected function getVariablesForJsonGeneration()
    {
        $variablesToCreate = $this->apiServerSideTracking->getVariablesList($this->measurementId);

        $variableId = 1;
        foreach ($variablesToCreate as $variableName => &$variableOptions) {
            if (isset($variableOptions['parameter'])) {
                foreach ($variableOptions['parameter'] as &$paramOptions) {
                    if (isset($paramOptions['type'])) {
                        $paramOptions['type'] = strtoupper($paramOptions['type']);
                    }
                }
            }

            $variableOptions['accountId'] = $this->accountId;
            $variableOptions['containerId'] = $this->containerId;
            $variableOptions['variableId'] = $variableId;
            $variableOptions['fingerprint'] = $this->fingerprint;
            $variableOptions['formatValue'] = new \stdClass();
            $variableId+=1;
        }

        return $variablesToCreate;
    }

    /**
     * @return array
     */
    protected function getTriggersForJsonGeneration()
    {
        $triggersToCreate = $this->apiServerSideTracking->getTriggersList($this->measurementId);

        $triggerId = 1;
        foreach ($triggersToCreate as $triggerName => &$triggerOptions) {
            if (isset($triggerOptions['customEventFilter'])) {
                foreach ($triggerOptions['customEventFilter'] as &$eventFilterOptions) {
                    if (isset($eventFilterOptions['parameter'])) {
                        foreach ($eventFilterOptions['parameter'] as &$paramOptions) {
                            if (isset($paramOptions['type'])) {
                                $paramOptions['type'] = strtoupper($paramOptions['type']);
                            }
                        }
                    }
                    if (isset($eventFilterOptions['type'])) {
                        if (isset($eventFilterOptions['type'])) {
                            $eventFilterOptions['type'] = strtoupper($eventFilterOptions['type']);
                        }
                    }
                }
            }
            if (isset($triggerOptions['filter'])) {
                foreach ($triggerOptions['filter'] as &$filterOptions) {
                    if (isset($filterOptions['parameter'])) {
                        foreach ($filterOptions['parameter'] as &$paramOptions) {
                            if (isset($paramOptions['type'])) {
                                $paramOptions['type'] = strtoupper($paramOptions['type']);
                            }
                        }
                    }
                    if (isset($filterOptions['type'])) {
                        $filterOptions['type'] = strtoupper($filterOptions['type']);
                    }
                }
            }
            if (isset($triggerOptions['type'])) {
                $triggerOptions['type'] = strtoupper(preg_replace('/(.)([A-Z])/', '$1_$2', $triggerOptions['type']));
            }

            $triggerOptions['accountId'] = $this->accountId;
            $triggerOptions['containerId'] = $this->containerId;
            $triggerOptions['triggerId'] = $triggerId;
            $triggerOptions['fingerprint'] = $this->fingerprint;
            $triggerId+=1;
        }

        return $triggersToCreate;
    }

    /**
     * @param array $triggers
     * @return array
     */
    public function getTagsForJsonGeneration($triggers)
    {
        $triggersMap = [];

        foreach ($triggers as $trigger) {
            $triggersMap[$trigger['name']] = $trigger['triggerId'];
        }

        $tagsToCreate = $this->apiServerSideTracking->getTagsList($triggersMap, $this->measurementId);

        $tagId = 1;
        foreach ($tagsToCreate as $tagName => &$tagOptions) {
            if (isset($tagOptions['parameter'])) {
                foreach ($tagOptions['parameter'] as $key => &$paramOptions) {
                    if (empty($paramOptions)) {
                        unset($tagOptions['parameter'][$key]);
                        continue;
                    }
                    if (isset($paramOptions['type'])) {
                        $paramOptions['type'] = strtoupper($paramOptions['type']);
                    }
                    if (isset($paramOptions['list'])) {
                        foreach ($paramOptions['list'] as &$listOptions) {
                            if (isset($listOptions['type'])) {
                                $listOptions["type"] = strtoupper($listOptions["type"]);
                            }
                            foreach ($listOptions["map"] as &$mapOptions) {
                                if (isset($mapOptions['type'])) {
                                    $mapOptions['type'] = strtoupper($mapOptions['type']);
                                }
                            }
                        }
                    }
                }
            }
            if (isset($tagOptions['tagFiringOption'])) {
                $tagOptions['tagFiringOption'] = strtoupper(preg_replace('/(.)([A-Z])/', '$1_$2', $tagOptions['tagFiringOption']));
            }

            $tagOptions['accountId'] = $this->accountId;
            $tagOptions['containerId'] = $this->containerId;
            $tagOptions['tagId'] = $tagId;
            $tagOptions['fingerprint'] = $this->fingerprint;
            $tagId+=1;
        }

        return $tagsToCreate;
    }
}
