#include <QDebug>
#include "Manager/SInfoManager.h"
#include "CalendarModel.h"

CalendarModel::CalendarModel()
{

}

void CalendarModel::goToMonth(QDate month)
{
    setEventMonthLoading(true);
    setEventDayLoading(true);
    month.setDate(month.year(), month.month(), 1);
    qDebug() << "get for month : " << month;
    BEGIN_REQUEST_ADV(this, "onLoadingEventDone", "onLoadingEventFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(month.toString("yyyy-MM-dd"));
        GET(API::DP_CALENDAR, API::GR_CALENDAR);
    }
    END_REQUEST;
    month.setDate(month.year(), month.month() - 1, 1);
    BEGIN_REQUEST_ADV(this, "onLoadingEventDone", "onLoadingEventFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(month.toString("yyyy-MM-dd"));
        GET(API::DP_CALENDAR, API::GR_CALENDAR);
    }
    END_REQUEST;
    month.setDate(month.year(), month.month() + 2, 1);
    BEGIN_REQUEST_ADV(this, "onLoadingEventDone", "onLoadingEventFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(month.toString("yyyy-MM-dd"));
        GET(API::DP_CALENDAR, API::GR_CALENDAR);
    }
    END_REQUEST;
    m_currentMonth = month;
}

void CalendarModel::getEventInfo(int id)
{
    BEGIN_REQUEST_ADV(this, "onGetEventDone", "onGetEventFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(id);
        GET(API::DP_CALENDAR, API::GR_EVENT);
    }
    END_REQUEST;
}

void CalendarModel::addEvent(QString title, QString message, int projectId, QDateTime begin, QDateTime end, QVariantList users)
{
    BEGIN_REQUEST_ADV(this, "onAddEventDone", "onAddEventFail");
    {
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", projectId);
        ADD_FIELD("title", title);
        ADD_FIELD("description", message);
        ADD_FIELD("begin", begin.toString("yyyy-MM-dd hh:mm:ss"));
        ADD_FIELD("end", end.toString("yyyy-MM-dd hh:mm:ss"));
        ADD_FIELD("icon", "");
        ADD_FIELD("typeId", 1);
        QList<int> idToAdd;
        for (QVariant var : users)
        {
            QVariantList l = var.toList();
            if (l[1].toBool())
                idToAdd.push_back(l[0].toInt());
        }
        ADD_ARRAY("users");
        for (int item : idToAdd)
            ADD_FIELD_ARRAY(item, "users");
        POST(API::DP_CALENDAR, API::PR_POST_EVENT);
        GENERATE_JSON_DEBUG;
    }
    END_REQUEST;
}

void CalendarModel::editEvent(int eventId, QString title, QString message, int projectId, QDateTime begin, QDateTime end, QVariantList users)
{
    BEGIN_REQUEST_ADV(this, "onEditEventDone", "onEditEventFail");
    {
        ADD_FIELD("eventId", eventId);
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", projectId);
        ADD_FIELD("title", title);
        ADD_FIELD("description", message);
        ADD_FIELD("begin", begin.toString("yyyy-MM-dd hh:mm:ss"));
        ADD_FIELD("end", end.toString("yyyy-MM-dd hh:mm:ss"));
        ADD_FIELD("icon", "");
        ADD_FIELD("typeId", 1);
        ADD_ARRAY("toAddUsers");
        ADD_ARRAY("toRemoveUsers");
        m_usersForEvents[PUT(API::DP_CALENDAR, API::PUTR_EDIT_EVENT)] = users;
        GENERATE_JSON_DEBUG;
    }
    END_REQUEST;
}

void CalendarModel::removeEvent(EventModelData *event)
{
    BEGIN_REQUEST_ADV(this, "onRemoveEventDone", "onRemoveEventFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(event->id());
        m_eventsToRemove[DELETE_REQ(API::DP_CALENDAR, API::DR_REMOVE_EVENT)] = event;
    }
    END_REQUEST;
}

void CalendarModel::getEventForDay(QDate date)
{

}

void CalendarModel::loadEventDay(QDate date)
{
    m_eventDay.clear();
    QMultiMap<int, EventModelData*>::iterator it = m_eventsLoaded.find(date.year() * 10000 + date.month() * 100 + date.day());
    for (; it != m_eventsLoaded.end(); ++it)
    {
        QDate dateItem = it.value()->beginDate().date();
        if (dateItem.day() == date.day() && dateItem.month() == date.month() && date.year() == date.year())
            m_eventDay.push_back(it.value());
    }
    emit eventDayChanged(eventDay());
}

int CalendarModel::getEventDayCount(QDate date)
{
    return m_eventsLoaded.count(CONVERT_TO_DATE_ID(date));
}

void CalendarModel::updateUser(EventModelData *event, QVariantList users, bool isAdd)
{
    QList<int> idToAdd;
    QList<int> idToRemove;
    for (QVariant var : users)
    {
        QVariantList l = var.toList();
        if (l[1].toBool() == false && !isAdd)
            idToRemove.push_back(l[0].toInt());
        else if (l[1].toBool())
            idToAdd.push_back(l[0].toInt());
    }
    BEGIN_REQUEST_ADV(this, "onSetParticipantDone", "onSetParticipantFail");
    {
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("eventId", event->id());
        ADD_ARRAY("toAdd");
        ADD_ARRAY("toRemove");
        for (int item : idToAdd)
            ADD_FIELD_ARRAY(item, "toAdd");
        if (!isAdd)
        {
            for (int item : idToRemove)
                ADD_FIELD_ARRAY(item, "toRemove");
        }
        m_eventsUserLink[PUT(API::DP_CALENDAR, API::PUTR_SET_EVENT_PARTICIPANT)] = event;
    }
    END_REQUEST;
}

void CalendarModel::onLoadingEventDone(int id, QByteArray array)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.5.1")
    {
        onLoadingEventFail(id, array);
        return;
    }
    for (QJsonValueRef ref : obj["array"].toObject()["events"].toArray())
    {
        QJsonObject event = ref.toObject();
        EventModelData *eventModel = nullptr;
        for (EventModelData *item : m_eventsLoaded.values())
        {
            if (item->id() == event["id"].toInt())
            {
                eventModel = item;
                break;
            }
        }
        if (!eventModel)
        {
            eventModel = new EventModelData(event);
            QDate date = eventModel->beginDate().date();
            m_eventsLoaded.insert(CONVERT_TO_DATE_ID(date), eventModel);
        }
        else
            eventModel->modifyByJsonObject(event);
    }
    setEventMonthLoading(false);
    setEventDayLoading(false);
    emit eventDayChanged(eventDay());
}

void CalendarModel::onLoadingEventFail(int id, QByteArray array)
{
    Q_UNUSED(id)
    Q_UNUSED(array)
    SInfoManager::GetManager()->emitError("Calendar", "Unable to retrieve events for month.");
}

void CalendarModel::onGetEventDone(int id, QByteArray array)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.5.1")
    {
        onLoadingEventFail(id, array);
        return;
    }
    int itemId = obj["id"].toInt();
    QDateTime t = JSON_TO_DATETIME(obj["beginDate"].toObject()["date"].toString());
    QMultiMap<int, EventModelData*>::iterator it = m_eventsLoaded.find(CONVERT_TO_DATE_ID(t.date()));
    for (; it != m_eventsLoaded.end(); ++it)
        if (it.value()->id() == itemId)
        {
            it.value()->modifyByJsonObject(obj);
        }
}

void CalendarModel::onGetEventFail(int id, QByteArray array)
{
    Q_UNUSED(id)
    Q_UNUSED(array)
    SInfoManager::GetManager()->emitError("Calendar", "Unable to retrieve event info.");
}

void CalendarModel::onEditEventDone(int id, QByteArray array)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.5.1")
    {
        onLoadingEventFail(id, array);
        return;
    }
    QDateTime t = JSON_TO_DATETIME(obj["beginDate"].toObject()["date"].toString());
    QMultiMap<int, EventModelData*>::iterator it = m_eventsLoaded.find(CONVERT_TO_DATE_ID(t.date()));
    for (; it != m_eventsLoaded.end(); ++it)
    {
        if (it.value()->id() == obj["id"].toInt())
        {
            it.value()->modifyByJsonObject(obj);
        }
    }
    m_usersForEvents.remove(id);
    SInfoManager::GetManager()->emitInfo("Event modified");
    emit eventDayChanged(eventDay());
}

void CalendarModel::onEditEventFail(int id, QByteArray array)
{

}

void CalendarModel::onAddEventDone(int id, QByteArray array)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.5.1")
    {
        onLoadingEventFail(id, array);
        return;
    }
    QDateTime t = JSON_TO_DATETIME(obj["beginDate"].toObject()["date"].toString());
    EventModelData *item = new EventModelData(obj);
    m_eventsLoaded.insert(CONVERT_TO_DATE_ID(t.date()), item);
    loadEventDay(m_currentDay);
    updateUser(item, m_usersForEvents[id]);
    m_usersForEvents.remove(id);
    emit eventDayChanged(eventDay());
    SInfoManager::GetManager()->emitInfo("Event added");
}

void CalendarModel::onAddEventFail(int id, QByteArray array)
{
}

void CalendarModel::onRemoveEventDone(int id, QByteArray array)
{
    Q_UNUSED(array);
    m_eventsLoaded.remove(CONVERT_TO_DATE_ID(m_eventsToRemove[id]->beginDate().date()), m_eventsToRemove[id]);
    m_eventDay.removeAll(m_eventsToRemove[id]);
    m_eventsToRemove.remove(id);
    emit eventDayChanged(eventDay());
    SInfoManager::GetManager()->emitInfo("Event deleted");
}

void CalendarModel::onRemoveEventFail(int id, QByteArray array)
{

}

void CalendarModel::onSetParticipantDone(int id, QByteArray array)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.5.1")
    {
        onLoadingEventFail(id, array);
        return;
    }
    m_eventsUserLink[id]->modifyByJsonObject(obj);
    m_eventsUserLink.remove(id);
}

void CalendarModel::onSetParticipantFail(int id, QByteArray array)
{

}

