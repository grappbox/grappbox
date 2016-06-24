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
    BEGIN_REQUEST_ADV(this, "onAddTicketDone", "onAddTicketFail");
    {
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

void BugTrackerModel::modifyTicket()
{

}

void BugTrackerModel::addUsersToTicket(int idTicket, int idUsers)
{

}

void BugTrackerModel::removeUsersToTicket(int idTicket, int idUsers)
{

}

void BugTrackerModel::addTagsToTicket(int idTicket, int idTag)
{

}

void BugTrackerModel::removeTagsToTicket(int idTicket, int idTag)
{

}

void BugTrackerModel::createAndAddTagsToTicket(int idTicket, QString tag)
{
    BEGIN_REQUEST_ADV(this, "onAddTagDone", "onAddTagFail");
    {
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
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject item = var.toObject();
        int id = item["id"].toInt();
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
    emit closedTicketsChanged(closedTickets());
}

void BugTrackerModel::onLoadClosedTicketFail(int id, QByteArray data)
{

}

void BugTrackerModel::onLoadOpenTicketDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject item = var.toObject();
        int id = item["id"].toInt();
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
    qDebug() << "Amount of ticket : " << m_openTickets.length();
    emit openTicketsChanged(openTickets());
}

void BugTrackerModel::onLoadOpenTicketFail(int id, QByteArray data)
{

}

void BugTrackerModel::onLoadYoursTicketDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject item = var.toObject();
        int id = item["id"].toInt();
        BugTrackerTicketData *ticket = nullptr;
        for (BugTrackerTicketData *item : m_yoursTickets)
            if (item->id() == id)
            {
                ticket = item;
                break;
            }
        if (ticket)
            ticket->modifyByJsonObject(item);
        else
            m_yoursTickets.push_back(new BugTrackerTicketData(item));
    }
    emit yoursTicketsChanged(yoursTickets());
}

void BugTrackerModel::onLoadYoursTicketFail(int id, QByteArray data)
{

}

void BugTrackerModel::onLoadCommentTicketDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    BugTrackerTicketData *ticket = nullptr;
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
    if (ticket == nullptr)
        for (BugTrackerTicketData *item : m_yoursTickets)
        {
            if (item->id() == m_loadingComment[id])
            {
                ticket = item;
                break;
            }
        }
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject obj = var.toObject();
        int id = obj["id"].toInt();
        BugTrackerComment *com = nullptr;
        for (BugTrackerComment *item : ticket->realListComment())
        {
            if (item->id() == id)
            {
                com = item;
                break;
            }
        }
        if (com)
            com->modifyByJsonObject(obj);
        else
            ticket->realListComment().push_back(new BugTrackerComment(obj));
    }
    ticket->setComments(ticket->comments());
}

void BugTrackerModel::onLoadCommentTicketFail(int id, QByteArray data)
{

}

void BugTrackerModel::onLoadTagsDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    for (QJsonValueRef var : obj["array"].toArray())
    {
        QJsonObject item = var.toObject();
        int id = item["id"].toInt();
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
    emit tagsChanged(tags());
}

void BugTrackerModel::onLoadTagsFail(int id, QByteArray data)
{

}

void BugTrackerModel::onAddTagDone(int id, QByteArray data)
{
    loadTags();
}

void BugTrackerModel::onAddTagFail(int id, QByteArray data)
{

}

void BugTrackerModel::onAddTicketDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    m_openTickets.push_front(new BugTrackerTicketData(obj));
    emit openTicketsChanged(openTickets());
}

void BugTrackerModel::onAddTicketFail(int id, QByteArray data)
{

}

void BugTrackerModel::closeTicket(int idTicket)
{

}

void BugTrackerModel::addComment(QString comment)
{

}

void BugTrackerModel::removeComment(int idComment)
{

}

void BugTrackerModel::modifyComment(QString comment)
{

}
