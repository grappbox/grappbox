#ifndef CALENDARMODEL_H
#define CALENDARMODEL_H

#include <QObject>
#include <QJsonObject>
#include "eventmodeldata.h"

#define CONVERT_TO_DATE_ID(date) (date.year() * 10000 + date.month() * 100 + date.day())

class CalendarModel : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QVariantList eventDay READ eventDay WRITE setEventDay NOTIFY eventDayChanged)
    Q_PROPERTY(bool eventDayLoading READ eventDayLoading WRITE setEventDayLoading NOTIFY eventDayLoadingChanged)
    Q_PROPERTY(bool eventMonthLoading READ eventMonthLoading WRITE setEventMonthLoading NOTIFY eventMonthLoadingChanged)

public:
    CalendarModel();

    Q_INVOKABLE void goToMonth(QDate month);
    Q_INVOKABLE void getEventInfo(int id);
    Q_INVOKABLE void addEvent(QString title, QString message, int projectId, QDateTime begin, QDateTime end, QVariantList users);
    Q_INVOKABLE void editEvent(int eventId, QString title, QString message, int projectId, QDateTime begin, QDateTime end, QVariantList users);
    Q_INVOKABLE void removeEvent(EventModelData *event);
    Q_INVOKABLE void getEventForDay(QDate date);
    Q_INVOKABLE void loadEventDay(QDate date);
    Q_INVOKABLE int getEventDayCount(QDate date);
    void updateUser(EventModelData *event, QVariantList users, bool isAdd = false);

    QVariantList eventDay() const
    {
        QVariantList ret;
        for (EventModelData *item : m_eventDay)
            if (item)
                ret.push_back(qVariantFromValue(item));
        return ret;
    }

    bool eventDayLoading() const
    {
        return m_eventDayLoading;
    }

    bool eventMonthLoading() const
    {
        return m_eventMonthLoading;
    }

signals:

    void eventDayChanged(QVariantList eventDay);
    void eventDayLoadingChanged(bool eventDayLoading);
    void eventMonthLoadingChanged(bool eventMonthLoading);

public slots:

    void onLoadingEventDone(int id, QByteArray array);
    void onLoadingEventFail(int id, QByteArray array);
    void onGetEventDone(int id, QByteArray array);
    void onGetEventFail(int id, QByteArray array);
    void onEditEventDone(int id, QByteArray array);
    void onEditEventFail(int id, QByteArray array);
    void onAddEventDone(int id, QByteArray array);
    void onAddEventFail(int id, QByteArray array);
    void onRemoveEventDone(int id, QByteArray array);
    void onRemoveEventFail(int id, QByteArray array);
    void onSetParticipantDone(int id, QByteArray array);
    void onSetParticipantFail(int id, QByteArray array);

    void setEventDay(QVariantList eventDay)
    {
        m_eventDay.clear();
        for (QVariant item : eventDay)
        {
            EventModelData *itemE = qobject_cast<EventModelData*>(item.value<EventModelData*>());
            m_eventDay.push_back(itemE);
        }
        emit eventDayChanged(eventDay);
    }

    void setEventDayLoading(bool eventDayLoading)
    {
        if (m_eventDayLoading == eventDayLoading)
            return;

        m_eventDayLoading = eventDayLoading;
        emit eventDayLoadingChanged(eventDayLoading);
    }

    void setEventMonthLoading(bool eventMonthLoading)
    {
        if (m_eventMonthLoading == eventMonthLoading)
            return;

        m_eventMonthLoading = eventMonthLoading;
        emit eventMonthLoadingChanged(eventMonthLoading);
    }

private:

    QMultiMap<int, EventModelData*> m_eventsLoaded;
    QList<EventModelData*> m_eventDay;
    QDate m_currentMonth;
    QDate m_currentDay;
    bool m_eventDayLoading;
    bool m_eventMonthLoading;

    QMap<int, QVariantList> m_usersForEvents;
    QMap<int, EventModelData*> m_eventsUserLink;
    QMap<int, EventModelData*> m_eventsToRemove;
};

#endif // CALENDARMODEL_H
