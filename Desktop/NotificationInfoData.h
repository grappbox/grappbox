#ifndef NOTIFICATIONINFODATA_H
#define NOTIFICATIONINFODATA_H

#include <QObject>
#include <QJsonObject>
#include "API/SDataManager.h"

class NotificationInfoData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QString type READ type WRITE setType NOTIFY typeChanged)
    Q_PROPERTY(QString message READ message WRITE setMessage NOTIFY messageChanged)
    Q_PROPERTY(QDateTime time READ time WRITE setTime NOTIFY timeChanged)

    QString m_type;

    QString m_message;

    QDateTime m_time;

public:
    explicit NotificationInfoData(QObject *parent = 0);
    NotificationInfoData(QJsonObject obj)
    {
        modifyByJson(obj);
    }

    void modifyByJson(QJsonObject obj)
    {
        m_type = obj["type"].toString();
        m_message = obj["message"].toString();
        m_time = JSON_TO_DATETIME(obj["createdAt"].toString());
        typeChanged(m_type);
        messageChanged(m_message);
        timeChanged(m_time);
    }

QString type() const
{
    return m_type;
}

QString message() const
{
    return m_message;
}

QDateTime time() const
{
    return m_time;
}

signals:

void typeChanged(QString type);

void messageChanged(QString message);

void timeChanged(QDateTime time);

public slots:
void setType(QString type)
{
    if (m_type == type)
        return;

    m_type = type;
    emit typeChanged(type);
}
void setMessage(QString message)
{
    if (m_message == message)
        return;

    m_message = message;
    emit messageChanged(message);
}
void setTime(QDateTime time)
{
    if (m_time == time)
        return;

    m_time = time;
    emit timeChanged(time);
}
};

#endif // NOTIFICATIONINFODATA_H
