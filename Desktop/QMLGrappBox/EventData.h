#ifndef EVENTDATA_H
#define EVENTDATA_H

#include <QObject>
#include <QDateTime>

class EventData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QString title READ title WRITE setTitle NOTIFY titleChanged)
    Q_PROPERTY(QString type READ type WRITE setType NOTIFY typeChanged)
    Q_PROPERTY(QString description READ description WRITE setDescription NOTIFY descriptionChanged)
    Q_PROPERTY(QDateTime startDate READ startDate WRITE setStartDate NOTIFY startDateChanged)
    Q_PROPERTY(QDateTime endDate READ endDate WRITE setEndDate NOTIFY endDateChanged)

public:
    EventData() {}

    int id() const
    {
        return m_id;
    }

    QString type() const
    {
        return m_type;
    }

    QString description() const
    {
        return m_description;
    }

    QDateTime startDate() const
    {
        return m_startDate;
    }

    QDateTime endDate() const
    {
        return m_endDate;
    }

    QString title() const
    {
        return m_title;
    }

signals:

    void idChanged(int id);

    void typeChanged(QString type);

    void descriptionChanged(QString description);

    void startDateChanged(QDateTime startDate);

    void endDateChanged(QDateTime endDate);

    void titleChanged(QString title);

public slots:

void setId(int id)
{
    if (m_id == id)
        return;

    m_id = id;
    emit idChanged(id);
}

void setType(QString type)
{
    if (m_type == type)
        return;

    m_type = type;
    emit typeChanged(type);
}

void setDescription(QString description)
{
    if (m_description == description)
        return;

    m_description = description;
    emit descriptionChanged(description);
}

void setStartDate(QDateTime startDate)
{
    if (m_startDate == startDate)
        return;

    m_startDate = startDate;
    emit startDateChanged(startDate);
}

void setEndDate(QDateTime endDate)
{
    if (m_endDate == endDate)
        return;

    m_endDate = endDate;
    emit endDateChanged(endDate);
}

void setTitle(QString title)
{
    if (m_title == title)
        return;

    m_title = title;
    emit titleChanged(title);
}

private:

int m_id;
QString m_type;
QString m_description;
QDateTime m_startDate;
QDateTime m_endDate;
QString m_title;
};

#endif // EVENTDATA_H

