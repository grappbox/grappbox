#include <QDebug>
#include "BugTrackerModel.h"

BugTrackerModel::BugTrackerModel() : QObject(nullptr)
{
}

void BugTrackerModel::loadTags()
{
    BEGIN_REQUEST_ADV(this, "onLoadTagsDone", "onLoadTagsFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_BUGTRACKER, API::GR_PROJECTBUGTAG_ALL);
    }
    END_REQUEST;
}

void BugTrackerModel::loadClosedTickets()
{
    BEGIN_REQUEST_ADV(this, "onLoadClosedTicketDone", "onLoadClosedTicketFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_BUGTRACKER, API::GR_BUG_CLOSED);
        GENERATE_JSON_DEBUG;
    }
    END_REQUEST;
}

void BugTrackerModel::loadOpenTickets()
{
    BEGIN_REQUEST_ADV(this, "onLoadOpenTicketDone", "onLoadOpenTicketFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_BUGTRACKER, API::GR_BUG_OPEN);
    }
    END_REQUEST;
}

void BugTrackerModel::loadYoursTickets()
{
    BEGIN_REQUEST_ADV(this, "onLoadYoursTicketDone", "onLoadYoursTicketFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        ADD_URL_FIELD(API::SDataManager::GetDataManager()->user()->id());
        GET(API::DP_BUGTRACKER, API::GR_BUG_YOURS);
    }
    END_REQUEST;
}

void BugTrackerModel::loadCommentTicket(int id)
{
    qDebug() << m_openTickets.length();
    BEGIN_REQUEST_ADV(this, "onLoadCommentTicketDone", "onLoadCommentTicketFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        ADD_URL_FIELD(id);
        m_loadingComment[GET(API::DP_BUGTRACKER, API::GR_BUGCOMMENT)] = id;
    }
    END_REQUEST;
}

void BugTrackerModel::addTicket(QString title, QString message, QVariantList users, QVariantList tags)
{
	Q_UNUSED(users)
	Q_UNUSED(tags)
    BEGIN_REQUEST_ADV(this, "onAddTicketDone", "onAddTicketFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
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

void BugTrackerModel::modifyTicket(int idTicket, QString title, QString message)
{
    BEGIN_REQUEST_ADV(this, "onModifyTicketDone", "onModifyTicketFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("bugId", idTicket);
        ADD_FIELD("title", title);
        ADD_FIELD("description", message);
        ADD_FIELD("stateId", 1);
        ADD_FIELD("stateName", "To Do");
        ADD_FIELD("clientOrigin", false);
        PUT(API::DP_BUGTRACKER, API::PUTR_EDIT_BUG);
    }
    END_REQUEST;
}

void BugTrackerModel::addUsersToTicket(int idTicket, int idUsers)
{
    BEGIN_REQUEST_ADV(this, "onAddUsersDone", "onAddUsersFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("bugId", idTicket);
        ADD_ARRAY("toAdd");
        ADD_FIELD_ARRAY(idUsers, "toAdd");
        ADD_ARRAY("toRemove");
        PUT(API::DP_BUGTRACKER, API::PUTR_ASSIGNUSER_BUG);
    }
    END_REQUEST;
}

void BugTrackerModel::removeUsersToTicket(int idTicket, int idUsers)
{
    BEGIN_REQUEST_ADV(this, "onRemoveUserDone", "onRemoveUserFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("bugId", idTicket);
        ADD_ARRAY("toAdd");
        ADD_ARRAY("toRemove");
        ADD_FIELD_ARRAY(idUsers, "toRemove");
        PUT(API::DP_BUGTRACKER, API::PUTR_ASSIGNUSER_BUG);
    }
    END_REQUEST;
}

void BugTrackerModel::addTagsToTicket(int idTicket, int idTag)
{
    BEGIN_REQUEST_ADV(this, "onAssignTagDone", "onAssignTagFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("bugId", idTicket);
        ADD_FIELD("tagId", idTag);
        m_assignTags[PUT(API::DP_BUGTRACKER, API::PUTR_ASSIGNTAG)] = idTicket;
    }
    END_REQUEST;
}

void BugTrackerModel::removeTagsToTicket(int idTicket, int idTag)
{
    BEGIN_REQUEST_ADV(this, "onRemoveTagsToTicketDone", "onRemoveTagsToTicketFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(idTicket);
        ADD_URL_FIELD(idTag);
        DELETE_REQ(API::DP_BUGTRACKER, API::DR_REMOVE_TAG_TO_BUG);
    }
    END_REQUEST;
}

void BugTrackerModel::removeTags(int idTag)
{
    BEGIN_REQUEST_ADV(this, "onDeleteTagDone", "onDeleteTagFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(idTag);
        DELETE_REQ(API::DP_BUGTRACKER, API::DR_REMOVE_BUGTAG);
    }
    END_REQUEST;
}

void BugTrackerModel::createAndAddTagsToTicket(int idTicket, QString tag)
{
    BEGIN_REQUEST_ADV(this, "onAddTagDone", "onAddTagFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", PROJECT);
        ADD_FIELD("name", tag);
        if (idTicket == -1)
            POST(API::DP_BUGTRACKER, API::PR_CREATE_BUG_TAG);
        else
            m_addingTags[POST(API::DP_BUGTRACKER, API::PR_CREATE_BUG_TAG)] = idTicket;
    }
    END_REQUEST;
}

void BugTrackerModel::onLoadClosedTicketDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QList<int> idPresent;
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject item = var.toObject();
        int id = item["id"].toInt();
        idPresent.push_back(id);
        BugTrackerTicketData *ticket = nullptr;
        for (BugTrackerTicketData *item : m_closedTickets)
            if (item->id() == id)
            {
                ticket = item;
                break;
            }
        if (ticket)
            ticket->modifyByJsonObject(item);
        else
            m_closedTickets.push_back(new BugTrackerTicketData(item));
    }
    QList<BugTrackerTicketData*> toRemove;
    for (BugTrackerTicketData *item : m_closedTickets)
        if (!idPresent.contains(item->id()))
            toRemove.push_back(item);
    for (BugTrackerTicketData *item : toRemove)
        m_closedTickets.removeAll(item);
    emit closedTicketsChanged(closedTickets());
}

void BugTrackerModel::onLoadClosedTicketFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onLoadOpenTicketDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QList<int> idPresent;
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject item = var.toObject();
        int id = item["id"].toInt();
        idPresent.push_back(id);
        BugTrackerTicketData *ticket = nullptr;
        for (BugTrackerTicketData *item : m_openTickets)
            if (item->id() == id)
            {
                ticket = item;
                break;
            }
        if (ticket)
            ticket->modifyByJsonObject(item);
        else
            m_openTickets.push_back(new BugTrackerTicketData(item));
    }
    QList<BugTrackerTicketData*> toRemove;
    for (BugTrackerTicketData *item : m_openTickets)
        if (!idPresent.contains(item->id()))
            toRemove.push_back(item);
    for (BugTrackerTicketData *item : toRemove)
        m_openTickets.removeAll(item);
    emit openTicketsChanged(openTickets());
}

void BugTrackerModel::onLoadOpenTicketFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onLoadYoursTicketDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QList<int> idPresent;
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject item = var.toObject();
        int id = item["id"].toInt();
        idPresent.push_back(id);
        BugTrackerTicketData *ticket = nullptr;
        for (BugTrackerTicketData *item : m_closedTickets)
            if (item->id() == id)
            {
                ticket = item;
                break;
            }
        if (ticket == nullptr)
        {
            for (BugTrackerTicketData *item : m_openTickets)
                if (item->id() == id)
                {
                    ticket = item;
                    break;
                }
        }
        if (ticket)
            ticket->modifyByJsonObject(item);
        else
        {
            BugTrackerTicketData *newTicket = new BugTrackerTicketData(item);
            m_yoursTickets.push_back(newTicket);
            if (newTicket->closeDate().isNull())
                m_openTickets.push_back(newTicket);
            else
                m_closedTickets.push_back(newTicket);
        }
    }
    QList<BugTrackerTicketData*> toRemove;
    for (BugTrackerTicketData *item : m_yoursTickets)
        if (!idPresent.contains(item->id()))
            toRemove.push_back(item);
    for (BugTrackerTicketData *item : toRemove)
        m_yoursTickets.removeAll(item);
    emit yoursTicketsChanged(yoursTickets());
}

void BugTrackerModel::onLoadYoursTicketFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onLoadCommentTicketDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    BugTrackerTicketData *ticket = nullptr;
    qDebug() << m_closedTickets.size();
    qDebug() << m_openTickets.size();
    for (BugTrackerTicketData *item : m_closedTickets)
    {
        if (item->id() == m_loadingComment[id])
        {
            ticket = item;
            break;
        }
    }
    if (ticket == nullptr)
        for (BugTrackerTicketData *item : m_openTickets)
        {
            if (item->id() == m_loadingComment[id])
            {
                ticket = item;
                break;
            }
        }
    qDebug() << ticket;
    if (obj["array"].toArray().size() == 0)
    {
        ticket->realListComment().clear();
        ticket->setComments(ticket->comments());
        return;
    }
    QList<int> idKeep;
    QList<BugTrackerComment*> list = ticket->realListComment();
    for (QJsonValueRef var : obj["array"].toArray())
    {
        qDebug() << "Load new comment :)";
        QJsonObject obj = var.toObject();
        int idComment = obj["id"].toInt();
        idKeep.push_back(idComment);
        BugTrackerComment *com = nullptr;
        for (BugTrackerComment *item : ticket->realListComment())
        {
            if (item->id() == idComment)
            {
                com = item;
                break;
            }
        }
        if (com)
            com->modifyByJsonObject(obj);
        else
            list.push_back(new BugTrackerComment(obj));
    }
    QList<BugTrackerComment*> toRemove;
    for (BugTrackerComment *item : list)
    {
        if (item == nullptr || !idKeep.contains(item->id()))
            toRemove.push_back(item);
    }
    for (BugTrackerComment *item : toRemove)
        list.removeAll(item);
    QVariantList newComments;
    for (BugTrackerComment *item : list)
        newComments.push_back(qVariantFromValue(item));
    ticket->setComments(newComments);
}

void BugTrackerModel::onLoadCommentTicketFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onLoadTagsDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QList<int> idPresent;
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject item = var.toObject();
        int id = item["id"].toInt();
        idPresent.push_back(id);
        BugTrackerTags *ticket = nullptr;
        for (BugTrackerTags *item : m_tags)
            if (item->id() == id)
            {
                ticket = item;
                break;
            }
        if (ticket)
        {
            ticket->setName(item["name"].toString());
        }
        else
        {
            ticket = new BugTrackerTags();
            ticket->setId(id);
            ticket->setName(item["name"].toString());
            m_tags.push_back(ticket);
        }
    }
    QList<BugTrackerTags*> toRemove;
    for (BugTrackerTags *item : m_tags)
        if (!idPresent.contains(item->id()))
            toRemove.push_back(item);
    for (BugTrackerTags *item : toRemove)
        m_tags.removeAll(item);
    emit tagsChanged(tags());
}

void BugTrackerModel::onLoadTagsFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onAddTagDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();

    loadTags();
    if (m_addingTags.contains(id))
    {
        addTagsToTicket(m_addingTags[id], obj["id"].toInt());
    }
}

void BugTrackerModel::onAddTagFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onAddTicketDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    m_openTickets.push_front(new BugTrackerTicketData(obj));
    emit openTicketsChanged(openTickets());
}

void BugTrackerModel::onAddTicketFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onAddUsersDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    int idTicket = obj["id"].toInt();
    BugTrackerTicketData *ticket = nullptr;
    for (BugTrackerTicketData *item : m_openTickets)
    {
        if (item->id() == idTicket)
            ticket = item;
    }
    if (ticket == nullptr)
        for (BugTrackerTicketData *item : m_closedTickets)
        {
            if (item->id() == idTicket)
                ticket = item;
        }
    if (ticket == nullptr)
    {
        loadClosedTickets();
        loadOpenTickets();
        loadYoursTickets();
    }
    else
    {
        ticket->modifyByJsonObject(obj);
    }
}

void BugTrackerModel::onAddUsersFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onAssignTagDone(int id, QByteArray data)
{
    int idTicket = m_assignTags[id];
    BugTrackerTicketData *ticket = getTicketById(idTicket);
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    if (ticket == nullptr)
    {
        // Put error message here !!!!
    }
    QVariantList l = ticket->tags();
    l.push_back(obj["tag"].toObject()["id"].toInt());
    ticket->setTags(l);
}

void BugTrackerModel::onAssignTagFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onRemoveTagsToTicketDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    loadClosedTickets();
    loadOpenTickets();
}

void BugTrackerModel::onRemoveTagsToTicketFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onDeleteTagDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    loadTags();
    loadClosedTickets();
    loadOpenTickets();
}

void BugTrackerModel::onDeleteTagFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onModifyTicketDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    int idTicket = obj["id"].toInt();
    BugTrackerTicketData *ticket = nullptr;
    for (BugTrackerTicketData *item : m_openTickets)
    {
        if (item->id() == idTicket)
            ticket = item;
    }
    if (ticket == nullptr)
        for (BugTrackerTicketData *item : m_closedTickets)
        {
            if (item->id() == idTicket)
                ticket = item;
        }
    if (ticket == nullptr)
    {
        loadClosedTickets();
        loadOpenTickets();
        loadYoursTickets();
    }
    else
    {
        ticket->modifyByJsonObject(obj);
    }
}

void BugTrackerModel::onModifyTicketFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onCloseDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    loadOpenTickets();
    loadClosedTickets();
    loadYoursTickets();
}

void BugTrackerModel::onCloseFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onAddCommentDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    int idTicket = obj["parentId"].toVariant().toInt();
    BugTrackerTicketData *ticket = nullptr;
    for (BugTrackerTicketData *item : m_openTickets)
    {
        if (item->id() == idTicket)
            ticket = item;
    }
    if (ticket == nullptr)
        for (BugTrackerTicketData *item : m_closedTickets)
        {
            if (item->id() == idTicket)
                ticket = item;
        }
    QVariantList comments = ticket->comments();
    comments.push_back(qVariantFromValue(new BugTrackerComment(obj)));
    ticket->setComments(comments);
}

void BugTrackerModel::onAddCommentFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onReopenDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    loadOpenTickets();
    loadClosedTickets();
    loadYoursTickets();
}

void BugTrackerModel::onReopenFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onRemoveCommentDone(int id, QByteArray data)
{
	Q_UNUSED(data)
    int idTicket = m_removeComment[id];
    m_removeComment.remove(id);
    loadCommentTicket(idTicket);
}

void BugTrackerModel::onRemoveCommentFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onEditCommentDone(int id, QByteArray data)
{
	Q_UNUSED(data)
    int idTicket = m_editComment[id];
    m_editComment.remove(id);
    loadCommentTicket(idTicket);
}

void BugTrackerModel::onEditCommentFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
        SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void BugTrackerModel::onRemoveUserDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    int idTicket = obj["id"].toInt();
    BugTrackerTicketData *ticket = nullptr;
    for (BugTrackerTicketData *item : m_openTickets)
    {
        if (item->id() == idTicket)
            ticket = item;
    }
    if (ticket == nullptr)
        for (BugTrackerTicketData *item : m_closedTickets)
        {
            if (item->id() == idTicket)
                ticket = item;
        }
    if (ticket == nullptr)
    {
        loadClosedTickets();
        loadOpenTickets();
        loadYoursTickets();
    }
    else
    {
        ticket->modifyByJsonObject(obj);
    }
}

void BugTrackerModel::onRemoveUserFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Bug tracker", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

BugTrackerTicketData *BugTrackerModel::getTicketById(int id)
{
    for (BugTrackerTicketData *ticket : m_closedTickets)
        if (ticket->id() == id)
            return ticket;
    for (BugTrackerTicketData *ticket : m_openTickets)
        if (ticket->id() == id)
            return ticket;
    return nullptr;
}

void BugTrackerModel::closeTicket(int idTicket)
{
    BEGIN_REQUEST_ADV(this, "onCloseDone", "onCloseFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(idTicket);
        DELETE_REQ(API::DP_BUGTRACKER, API::DR_CLOSE_TICKET_OR_COMMENT);
    }
    END_REQUEST;
}

void BugTrackerModel::reopenTicket(int idTicket)
{
    BEGIN_REQUEST_ADV(this, "onReopenDone", "onReopenFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(idTicket);
        PUT(API::DP_BUGTRACKER, API::PUTR_REOPEN_BUG);
    }
    END_REQUEST;
}

void BugTrackerModel::addComment(int idParent, QString comment)
{
    BEGIN_REQUEST_ADV(this, "onAddCommentDone", "onAddCommentFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", PROJECT);
        ADD_FIELD("parentId", idParent);
        ADD_FIELD("title", "");
        ADD_FIELD("description", comment);
        POST(API::DP_BUGTRACKER, API::PR_COMMENT_BUG);
    }
    END_REQUEST;
}

void BugTrackerModel::removeComment(int idComment, int idTicket)
{
    BEGIN_REQUEST_ADV(this, "onRemoveCommentDone", "onRemoveCommentFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(idComment);
        m_removeComment[DELETE_REQ(API::DP_BUGTRACKER, API::DR_CLOSE_TICKET_OR_COMMENT)] = idTicket;
    }
    END_REQUEST;
}

void BugTrackerModel::modifyComment(int idComment, QString comment, int idTicket)
{
    BEGIN_REQUEST_ADV(this, "onEditCommentDone", "onEditCommentFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", PROJECT);
        ADD_FIELD("commentId", idComment);
        ADD_FIELD("title", "");
        ADD_FIELD("description", comment);
        m_editComment[PUT(API::DP_BUGTRACKER, API::PUTR_EDIT_COMMENTBUG)] = idTicket;
    }
    END_REQUEST;
}
