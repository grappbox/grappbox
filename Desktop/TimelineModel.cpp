#include <QDebug>
#include "TimelineModel.h"

TimelineModel::TimelineModel(QObject *parent) : QObject(parent)
{
    m_numberLoading = 0;
}

void TimelineModel::OnTimelineLoadDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    /*if (info["return_code"].toString() == "3.4.9" || info["return_code"].toString() == "3.9.9")
    {
        return;
    }*/
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        TimelineMessageData *message = nullptr;
        QJsonObject item = ref.toObject();
        bool isClient = item["timelineId"].toInt() == m_idTimelineClient;
        bool hasToAdd = true;
        for (TimelineMessageData *mess : (isClient ? m_timelineClient : m_timelineTeam))
        {
            if (mess->id() == item["id"].toInt())
            {
                message = mess;
                hasToAdd = false;
                break;
            }
        }
        if (hasToAdd)
            message = new TimelineMessageData();
        message->setId(item["id"].toInt());
        message->setIsComment(false);
        message->setMessage(item["message"].toString());
        message->setTitle(item["title"].toString());
        UserData *userData = message->associatedUser();
        if (userData == nullptr)
            userData = new UserData();
        userData->setId(item["creator"].toObject()["id"].toInt());
        userData->setFirstName(item["creator"].toObject()["firstname"].toString());
        userData->setLastName(item["creator"].toObject()["lastname"].toString());
        message->setAssociatedUser(userData);
        if (item["editedAt"].isNull())
            message->setLastEdit(JSON_TO_DATETIME(item["createdAt"].toString()));
        else
            message->setLastEdit(JSON_TO_DATETIME(item["editedAt"].toString()));
        if (hasToAdd)
        {
            if (isClient)
                m_timelineClient.push_back(message);
            else
                m_timelineTeam.push_back(message);
        }
    }
    m_numberLoading--;
    if (m_numberLoading <= 0)
        setIsLoadingTimeline(false);
    emit timelineClientChanged(timelineClient());
    emit timelineTeamChanged(timelineTeam());
}

void TimelineModel::OnTimelineLoadFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_numberLoading--;
    if (m_numberLoading <= 0)
        setIsLoadingTimeline(false);
    SInfoManager::GetManager()->emitError("Timeline", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void TimelineModel::OnTimelineCommentLoadDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    /*if (info["return_code"].toString() == "3.4.9" || info["return_code"].toString() == "3.9.9")
    {
        return;
    }*/
    int realId = m_loadComment[id];
    TimelineMessageData *realMess = nullptr;
    for (TimelineMessageData *item : m_timelineClient)
    {
        if (item->id() == realId)
        {
            realMess = item;
            break;
        }
    }
    if (realMess == nullptr)
        for (TimelineMessageData *item : m_timelineTeam)
        {
            if (item->id() == realId)
            {
                realMess = item;
                break;
            }
        }
    QList<TimelineMessageData*> currentList = realMess->commentsList();
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        TimelineMessageData *message = nullptr;
        QJsonObject item = ref.toObject();
        bool hasToAdd = true;
        for (TimelineMessageData *mess : currentList)
        {
            if (mess->id() == item["id"].toInt())
            {
                message = mess;
                hasToAdd = false;
                break;
            }
        }
        if (hasToAdd)
            message = new TimelineMessageData();
        message->setId(item["id"].toInt());
        message->setIsComment(false);
        message->setMessage(item["comment"].toString());
        message->setTitle("");
        UserData *userData = message->associatedUser();
        if (userData == nullptr)
            userData = new UserData();
        userData->setId(item["creator"].toObject()["id"].toInt());
        userData->setFirstName(item["creator"].toObject()["firstname"].toString());
        userData->setLastName(item["creator"].toObject()["lastname"].toString());
        message->setAssociatedUser(userData);
        if (item["editedAt"].isNull())
            message->setLastEdit(JSON_TO_DATETIME(item["createdAt"].toString()));
        else
            message->setLastEdit(JSON_TO_DATETIME(item["editedAt"].toString()));
        if (hasToAdd)
        {
            currentList.push_back(message);
        }
    }
    realMess->setComments(currentList);
    m_loadComment.remove(id);
    if (m_loadComment.size() == 0)
        setIsLoadingComment(false);
}

void TimelineModel::OnTimelineCommentLoadFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
        SInfoManager::GetManager()->emitError("Timeline", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void TimelineModel::OnTimelineAddMessageDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject info = doc.object()["info"].toObject();
    QJsonObject obj = doc.object()["data"].toObject();

    TimelineMessageData *message = new TimelineMessageData();

    message->setId(obj["id"].toInt());
    message->setIsComment(!obj["parentId"].isNull());
    message->setMessage(obj[obj["parentId"].isNull() ? "message" : "comment"].toString());
    message->setTitle(obj[obj["parentId"].isNull() ? "title" : ""].toString());

    UserData *userData = message->associatedUser();
    if (userData == nullptr)
        userData = new UserData();
    userData->setId(obj["creator"].toObject()["id"].toInt());
    userData->setFirstName(obj["creator"].toObject()["firstname"].toString());
    userData->setLastName(obj["creator"].toObject()["lastname"].toString());
    message->setAssociatedUser(userData);

    if (obj["editedAt"].isNull())
        message->setLastEdit(JSON_TO_DATETIME(obj["createdAt"].toString()));
    else
        message->setLastEdit(JSON_TO_DATETIME(obj["editedAt"].toString()));

    if (obj["parentId"].isNull())
    {
        if (obj["timelineId"].toInt() == m_idTimelineClient)
        {
            m_timelineClient.push_front(message);
            emit timelineClientChanged(timelineClient());
        }
        else
        {
            m_timelineTeam.push_front(message);
            emit timelineTeamChanged(timelineTeam());
        }
    }
    else
    {
        TimelineMessageData *realMess = nullptr;
        qDebug() << obj["parentId"].toString();
        int parentId = obj["parentId"].toInt();
        for (TimelineMessageData *itemD : m_timelineClient)
        {
            if (itemD->id() == parentId)
            {
                realMess = itemD;
                break;
            }
        }
        if (realMess == nullptr)
            for (TimelineMessageData *itemD : m_timelineTeam)
            {
                if (itemD->id() == parentId)
                {
                    realMess = itemD;
                    break;
                }
            }

        QList<TimelineMessageData*> list = realMess->commentsList();
        list.push_back(message);
        realMess->setComments(list);
    }
}

void TimelineModel::OnTimelineAddMessageFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Timeline", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void TimelineModel::OnTimelineRemoveMessageDone(int id, QByteArray data)
{
    emit deleteSuccess();
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    int removeId = QVariant(obj["id"].toString()).toInt();
    if (m_deleteComment.contains(id))
    {
        int idParent = m_deleteComment[id];
        TimelineMessageData *realMess = nullptr;
        for (TimelineMessageData *item : m_timelineClient)
        {
            if (item->id() == idParent)
            {
                realMess = item;
                break;
            }
        }
        if (realMess == nullptr)
            for (TimelineMessageData *item : m_timelineTeam)
            {
                if (item->id() == idParent)
                {
                    realMess = item;
                    break;
                }
            }
        QList<TimelineMessageData*> list = realMess->commentsList();
        TimelineMessageData *toRemove = nullptr;
        for (TimelineMessageData *item : list)
        {
            if (item->id() == removeId)
            {
                toRemove = item;
                break;
            }
        }
        qDebug() << "Parent : " << realMess->id();
        qDebug() << "To remove : " << ((toRemove == nullptr) ? -1 : toRemove->id());
        qDebug() << list.contains(toRemove);
        list.removeAll(toRemove);
        realMess->setComments(list);
    }
    else
    {
        for (TimelineMessageData *item : m_timelineClient)
        {
            if (item->id() == removeId)
            {
                m_timelineClient.removeAll(item);
                emit timelineClientChanged(timelineClient());
                return;
            }
        }
        for (TimelineMessageData *item : m_timelineTeam)
        {
            if (item->id() == removeId)
            {
                m_timelineTeam.removeAll(item);
                emit timelineTeamChanged(timelineTeam());
                return;
            }
        }
    }
    emit closeCommentIfId(removeId);
}

void TimelineModel::OnTimelineRemoveMessageFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Timeline", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void TimelineModel::OnTimelineEditMessageDone(int id, QByteArray data)
{
    emit editSuccess();
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    int editId = obj["id"].toInt();
    if (obj.contains("parentId") && m_editComment.contains(id))
    {
        int idParent = m_editComment[id];
        TimelineMessageData *realMess = nullptr;
        for (TimelineMessageData *item : m_timelineClient)
        {
            if (item->id() == idParent)
            {
                realMess = item;
                break;
            }
        }
        if (realMess == nullptr)
            for (TimelineMessageData *item : m_timelineTeam)
            {
                if (item->id() == idParent)
                {
                    realMess = item;
                    break;
                }
            }
        QList<TimelineMessageData*> list = realMess->commentsList();
        for (TimelineMessageData *item : list)
        {
            if (item->id() == editId)
            {
                qDebug() << "Edit done";
                item->setTitle(obj["title"].toString());
                item->setMessage(obj["comment"].toString());
                item->setLastEdit(JSON_TO_DATETIME(obj["editedAt"].toString()));
                break;
            }
        }
        realMess->setComments(list);
    }
    else
    {
        for (TimelineMessageData *item : m_timelineClient)
        {
            if (item->id() == editId)
            {
                qDebug() << "Edit done";
                item->setTitle(obj["title"].toString());
                item->setMessage(obj["message"].toString());
                item->setLastEdit(JSON_TO_DATETIME(obj["editedAt"].toString()));
                emit timelineClientChanged(timelineClient());
                return;
            }
        }
        for (TimelineMessageData *item : m_timelineTeam)
        {
            if (item->id() == editId)
            {
                item->setTitle(obj["title"].toString());
                item->setMessage(obj["message"].toString());
                item->setLastEdit(JSON_TO_DATETIME(obj["editedAt"].toString()));
                emit timelineTeamChanged(timelineTeam());
                return;
            }
        }
    }
}

void TimelineModel::OnTimelineEditMessageFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Timeline", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void TimelineModel::OnGetTimelineDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    /*if (info["return_code"].toString() == "3.4.9" || info["return_code"].toString() == "3.9.9")
    {
        return;
    }*/
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject item = ref.toObject();
        if (item["typeId"].toInt() == 1)
        {
            m_idTimelineClient = item["id"].toInt();
        }
        else
        {
            m_idTimelineTeam = item["id"].toInt();
        }
    }
    loadNextTimelineContent(false);
    loadNextTimelineContent(true);
}

void TimelineModel::OnGetTimelineFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    setIsLoadingTimeline(false);
    SInfoManager::GetManager()->emitError("Timeline", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void TimelineModel::loadNextTimelineContent(bool isClient)
{
    m_numberLoading++;
    BEGIN_REQUEST_ADV(this, "OnTimelineLoadDone", "OnTimelineLoadFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(isClient ? m_idTimelineClient : m_idTimelineTeam);
        GET(API::DP_TIMELINE, API::GR_TIMELINE);
    }
    END_REQUEST;
}

void TimelineModel::loadComments(bool isClient, int id)
{
    BEGIN_REQUEST_ADV(this, "OnTimelineCommentLoadDone", "OnTimelineCommentLoadFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(isClient ? m_idTimelineClient : m_idTimelineTeam);
        ADD_URL_FIELD(id);
        m_loadComment[GET(API::DP_TIMELINE, API::GR_COMMENT_TIMELINE)] = id;
    }
    END_REQUEST;
    setIsLoadingComment(true);
}

void TimelineModel::addMessageTimeline(bool isClient, QString title, QString message)
{
    BEGIN_REQUEST_ADV(this, "OnTimelineAddMessageDone", "OnTimelineAddMessageFail");
    {
        ADD_URL_FIELD(isClient ? m_idTimelineClient : m_idTimelineTeam);
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_FIELD("title", title);
        ADD_FIELD("message", message);
        POST(API::DP_TIMELINE, API::PR_MESSAGE_TIMELINE);
    }
    END_REQUEST;
    setIsLoadingAction(true);
}

void TimelineModel::addMessageTimeline(int idParent, QString message)
{
    bool isClient = true;
    for (TimelineMessageData *item : m_timelineTeam)
    {
        if (item->id() == idParent)
        {
            isClient = false;
            break;
        }
    }
    BEGIN_REQUEST_ADV(this, "OnTimelineAddMessageDone", "OnTimelineAddMessageFail");
    {
        ADD_URL_FIELD(isClient ? m_idTimelineClient : m_idTimelineTeam);
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_FIELD("comment", message);
        ADD_FIELD("commentedId", idParent);
        POST(API::DP_TIMELINE, API::PR_COMMENT_TIMELINE);
        GENERATE_JSON_DEBUG;
    }
    END_REQUEST;
    setIsLoadingAction(true);
}

void TimelineModel::deleteMessageTimeline(int id, int parentId)
{
    int timelineId = -1;
    for (TimelineMessageData *item : m_timelineClient)
    {
        if (item->id() == (parentId == -1 ? id : parentId))
        {
            timelineId = m_idTimelineClient;
            break;
        }
    }
    if (timelineId == -1)
        for (TimelineMessageData *item : m_timelineTeam)
        {
            if (item->id() == (parentId == -1 ? id : parentId))
            {
                timelineId = m_idTimelineTeam;
                break;
            }
        }
    BEGIN_REQUEST_ADV(this, "OnTimelineRemoveMessageDone", "OnTimelineRemoveMessageFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        if (parentId != -1)
        {
            ADD_URL_FIELD(id);
            m_deleteComment[DELETE_REQ(API::DP_TIMELINE, API::DR_ARCHIVE_COMMENT_TIMELINE)] = parentId;
        }
        else
        {
            ADD_URL_FIELD(timelineId);
            ADD_URL_FIELD(id);
            DELETE_REQ(API::DP_TIMELINE, API::DR_ARCHIVE_MESSAGE_TIMELINE);
        }
    }
    END_REQUEST;
}

void TimelineModel::editMessageTimeline(int parentId, int id, QString title, QString message)
{
    int timelineId = -1;
    for (TimelineMessageData *item : m_timelineClient)
    {
        if (item->id() == (parentId == -1 ? id : parentId))
        {
            timelineId = m_idTimelineClient;
            break;
        }
    }
    if (timelineId == -1)
        for (TimelineMessageData *item : m_timelineTeam)
        {
            if (item->id() == (parentId == -1 ? id : parentId))
            {
                timelineId = m_idTimelineTeam;
                break;
            }
        }
    BEGIN_REQUEST_ADV(this, "OnTimelineEditMessageDone", "OnTimelineEditMessageFail");
    {
        ADD_URL_FIELD(timelineId);
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        if (parentId != -1)
        {
            ADD_FIELD("commentId", id);
            ADD_FIELD("comment", message);
            m_editComment[PUT(API::DP_TIMELINE, API::PUTR_EDIT_COMMENT_TIMELINE)] = parentId;
        }
        else
        {
            ADD_URL_FIELD(id);
            ADD_FIELD("title", title);
            ADD_FIELD("message", message);
            PUT(API::DP_TIMELINE, API::PUTR_EDIT_MESSAGE_TIMELINE);
        }
    }
    END_REQUEST;
}

void TimelineModel::loadTimelines()
{
    setIsLoadingTimeline(true);
    BEGIN_REQUEST_ADV(this, "OnGetTimelineDone", "OnGetTimelineFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_TIMELINE, API::GR_LIST_TIMELINE);
    }
    END_REQUEST;
}

void TimelineModel::addTicket(QString title, QString message)
{
    BEGIN_REQUEST_ADV(this, "OnAddTicketDone", "OnAddTicketFail");
    {
        EPURE_WARNING_INDEX
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_FIELD("projectId", PROJECT);
        ADD_FIELD("title", title);
        ADD_FIELD("description", message);
        ADD_FIELD("stateId", 1);
        ADD_FIELD("stateName", "To Do");
        ADD_FIELD("clientOrigin", false);
        POST(API::DP_BUGTRACKER, API::PR_CREATE_BUG);
    }
    END_REQUEST;
}

void TimelineModel::OnAddTicketDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    SInfoManager::GetManager()->info("The message has been added to the bug tracker.");
}

void TimelineModel::OnAddTicketFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Timeline", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}
