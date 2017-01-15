#include "NotificationInfoData.h"
#include "NotificationModel.h"
#include "Manager/SaveInfoManager.h"

NotificationModel::NotificationModel(QObject *parent) : QObject(parent)
{
    _timer = new QTimer();
    QObject::connect(_timer, SIGNAL(timeout()), this, SLOT(updateNotification()));
    _timer->start(TIME_NOTIFICATION_UPDATE);
}

void NotificationModel::updateNotification()
{
    if (USER_TOKEN == "")
        return;
    currentOffset = SaveInfoManager::get(NOTIFICATION_OFFSET).toInt();
    BEGIN_REQUEST_ADV(this, "OnUpdateDone", "OnUpdateFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD("false");
        ADD_URL_FIELD(currentOffset);
        ADD_URL_FIELD(LIMIT_UPDATE);
        GET(API::DP_PROJECT, API::GR_NOTIF);
    }
    END_REQUEST;
}

void NotificationModel::OnUpdateDone(int id, QByteArray array)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    currentOffset += obj["array"].toArray().size() - LIMIT_UPDATE;
    SaveInfoManager::set(NOTIFICATION_OFFSET, QVariant(currentOffset));
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        if (ref.toObject()["message"].toString().startsWith("{"))
            continue;
        NotificationInfoData *info = new NotificationInfoData(ref.toObject());
        m_notification.push_front(qVariantFromValue(info));
    }
    notificationChanged(notification());
}

void NotificationModel::OnUpdateFail(int id, QByteArray array)
{

}
