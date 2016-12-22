#include "NotificationInfoData.h"
#include "NotificationModel.h"

NotificationModel::NotificationModel(QObject *parent) : QObject(parent)
{
    _timer = new QTimer();
    QObject::connect(_timer, SIGNAL(timeout()), this, SLOT(updateNotification()));
    _timer->start(TIME_NOTIFICATION_UPDATE);
    qDebug() << "Initialized";
    currentOffset = 0;
}

void NotificationModel::updateNotification()
{
    qDebug() << "Request sent";
    if (USER_TOKEN == "")
        return;
    qDebug() << "Token not null";
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
    currentOffset += obj["array"].toArray().size();
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        NotificationInfoData *info = new NotificationInfoData(ref.toObject());
        m_notification.push_front(qVariantFromValue(info));
    }
    notificationChanged(notification());
}

void NotificationModel::OnUpdateFail(int id, QByteArray array)
{

}
