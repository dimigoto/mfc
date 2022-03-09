<?php

declare(strict_types=1);

namespace common\modules\mfc\controllers\backend;

use common\dataProviders\CustomArrayDataProvider;
use common\modules\mfc\jobs\MfcRequestsSendJob;
use common\modules\mfc\MfcModule;
use common\modules\mfc\models\MfcRequest;
use common\modules\mfc\repositories\MfcRequestRepository;
use common\modules\userUniver\services\UserRoleService;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Неотправленные заявки "Единого окна", по которым превышено количество попыток
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                    'resend' => ['GET'],
                    'resend-process' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'resend',
                            'resend-process',
                        ],
                        'allow' => true,
                        'roles' => [UserRoleService::ROLE_ADMIN],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список неотправленных заявок, по которым превышено количество попыток
     */
    public function actionIndex(): string
    {
        $mfcRequestRepository = new MfcRequestRepository();

        $dataProvider = new CustomArrayDataProvider([
            'modelClass' => MfcRequest::class,
            'allModels' => $mfcRequestRepository->findAllStalled(),
            'pagination' => false,
        ]);

        return $this->render(
            'index',
            compact(
                'dataProvider',
            )
        );
    }

    /**
     * Отправка заявок вручную
     *
     * @return string
     */
    public function actionResend(): string
    {
        return $this->render('resend');
    }

    /**
     * Обработка запроса на отправку заявок в 1С
     *
     * @return array
     */
    public function actionResendProcess(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [];

        try {
            Yii::$app->queue->push(new MfcRequestsSendJob());

            $result['status'] = 'success';
            $result['message'] = MfcModule::t('backend', 'MFC_RESEND_MESSAGE_SUCCESS');
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = $e->getMessage();
        }

        return $result;
    }
}
