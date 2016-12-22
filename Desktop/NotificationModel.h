#ifndef NOTIFICATIONMODEL_H
#define NOTIFICATIONMODEL_H

#include <QObject>
#include <QTimer>
#include <QJsonObject>
#include <QVariant>
#include "API/SDataManager.h"
#include <QDebug>

#define TIME_NOTIFICATION_UPDATE 5000
#define LIMIT_UPDATE 15

class NotificationModel : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QVariantList notification READ notification WRITE setNotification NOTIFY notificationChanged)

    QVariantList m_notification;
    QTimer *_timer;
    int currentOffset;

public:
    explicit NotificationModel(QObject *parent = 0);

    QVariantList notification() const
    {
        return m_notification;
    }

signals:

void notificationChanged(QVariantList notification);

public slots:

    void updateNotification();
    void OnUpdateDone(int id, QByteArray array);
    void OnUpdateFail(int id, QByteArray array);

    void setNotification(QVariantList notification)
    {
        if (m_notification == notification)
            return;

        m_notification = notification;
        emit notificationChanged(notification);
    }
};

#endif // NOTIFICATIONMODEL_H
