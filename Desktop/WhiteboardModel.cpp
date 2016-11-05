#include <QDebug>
#include <QTimeZone>
#include "WhiteboardModel.h"

WhiteboardModel::WhiteboardModel(QObject *parent) : QObject(parent)
{
    m_currentItem = -1;
    m_timer = new QTimer();
    QObject::connect(m_timer, SIGNAL(timeout()), this, SLOT(updateWhiteboard()));
}

void WhiteboardModel::updateList()
{
    BEGIN_REQUEST_ADV(this, "onUpdateListDone", "onUpdateListFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_WHITEBOARD, API::GR_LIST_WHITEBOARD);
    }
    END_REQUEST;
}

void WhiteboardModel::createWhiteboard(QString name)
{
    BEGIN_REQUEST_ADV(this, "onCreateWhiteboardDone", "onCreateWhiteboardFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_FIELD("projectId", PROJECT);
        ADD_FIELD("whiteboardName", name);
        POST(API::DP_WHITEBOARD, API::PR_CREATE_WHITEBOARD);
    }
    END_REQUEST;
}

void WhiteboardModel::deleteWhiteboard(int id)
{
    BEGIN_REQUEST_ADV(this, "onDeleteWhiteboardDone", "onDeleteWhiteboardFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(id);
        DELETE_REQ(API::DP_WHITEBOARD, API::DR_DELETE_WHITEBOARD);
    }
    END_REQUEST;
}

void WhiteboardModel::openWhiteboard(int id)
{
    BEGIN_REQUEST_ADV(this, "onOpenWhiteboardDone", "onOpenWhiteboardFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(id);
        GET(API::DP_WHITEBOARD, API::GR_OPEN_WHITEBOARD);
    }
    END_REQUEST;
}

void WhiteboardModel::closeWhiteboard()
{
    BEGIN_REQUEST_ADV(this, "onCloseWhiteboardDone", "onCloseWhiteboardFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(m_currentId);
        PUT(API::DP_WHITEBOARD, API::PUTR_CLOSE_WHITEBOARD);
    }
    END_REQUEST;
    m_currentId = -1;
    setCurrentItem(-1);
}

int WhiteboardModel::pushObject(QVariantMap obj)
{
    int ret;
    BEGIN_REQUEST_ADV(this, "onPushObjectDone", "onPushObjectFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(m_currentId);
        ADD_FIELD("object#object", qVariantFromValue(WhiteboardData::JSToJson(obj)));
        GENERATE_JSON_DEBUG;
        ret = PUT(API::DP_WHITEBOARD, API::PUTR_PUSH_WHITEBOARD);
    }
    END_REQUEST;
    return ret;
}

void WhiteboardModel::pullObject()
{
    WhiteboardData *item = qobject_cast<WhiteboardData*>(m_whiteboardList[m_currentItem].value<WhiteboardData*>());
    BEGIN_REQUEST_ADV(this, "onPullObjectDone", "onPullObjectFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(m_currentId);
        ADD_FIELD("lastUpdate", item->editDate().toString("yyyy-MM-dd hh:mm:ss"));
        POST(API::DP_WHITEBOARD, API::PR_PULL_WHITEBOARD);
    }
    END_REQUEST;
   // item->setEditDate(QDateTime(QDate(), QTime(), QTimeZone::utc()));
}

void WhiteboardModel::removeObjectAt(QVariantMap center, float radius)
{
    BEGIN_REQUEST_ADV(this, "onRemoveObjectDone", "onRemoveObjectFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(m_currentId);
        ADD_FIELD("object#center", qVariantFromValue(WhiteboardData::JSToJson(center)));
        ADD_FIELD("radius", radius);
        GENERATE_JSON_DEBUG;
        DELETE_REQ(API::DP_WHITEBOARD, API::DR_DELETE_OBJECT);
    }
    END_REQUEST;
}

void WhiteboardModel::onUpdateListDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.10.1" &&
            info["return_code"].toString() != "1.10.3")
    {
        onUpdateListFail(id, data);
        return;
    }
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject objRef = ref.toObject();
        qDebug() << objRef["id"].toInt();
        WhiteboardData *existingWhite = nullptr;
        for (QVariant item : m_whiteboardList)
        {
            WhiteboardData *itemWhite = qobject_cast<WhiteboardData*>(item.value<WhiteboardData*>());
            if (itemWhite->id() == objRef["id"].toInt())
            {
                existingWhite = itemWhite;
                break;
            }
        }
        if (existingWhite)
            existingWhite->modifyByJsonObject(objRef);
        else
        {
            WhiteboardData *newWhite = new WhiteboardData(objRef);
            m_whiteboardList.push_back(qVariantFromValue(newWhite));
        }
    }
    emit whiteboardListChanged(whiteboardList());
}

void WhiteboardModel::onUpdateListFail(int id, QByteArray data)
{
    SInfoManager::GetManager()->error("Whiteboard", "Unable to retrieve the list of whiteboards.");
}

void WhiteboardModel::onCreateWhiteboardDone(int id, QByteArray data)
{
    updateList();
}

void WhiteboardModel::onCreateWhiteboardFail(int id, QByteArray data)
{
    SInfoManager::GetManager()->error("Whiteboard", "Unable to create the new whiteboard.");
}

void WhiteboardModel::onDeleteWhiteboardDone(int id, QByteArray data)
{
    updateList();
}

void WhiteboardModel::onDeleteWhiteboardFail(int id, QByteArray data)
{
    SInfoManager::GetManager()->error("Whiteboard", "Unable to delete the whiteboard.");
}

void WhiteboardModel::onOpenWhiteboardDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.10.1" &&
            info["return_code"].toString() != "1.10.3")
    {
        onUpdateListFail(id, data);
        return;
    }
    WhiteboardData *item = nullptr;
    int i = 0;
    for (QVariant var : m_whiteboardList)
    {
        WhiteboardData *tmp = qobject_cast<WhiteboardData*>(var.value<WhiteboardData*>());
        if (tmp && tmp->id() == obj["id"].toInt())
        {
            setCurrentItem(i);
            m_currentId = tmp->id();
            item = tmp;
            break;
        }
        ++i;
    }
    item->setEditDate(JSON_TO_DATETIME(obj["updatedAt"].toString()));
    item->loadContent(obj["content"].toArray());
    m_timer->start(3000);
}

void WhiteboardModel::onOpenWhiteboardFail(int id, QByteArray data)
{
    SInfoManager::GetManager()->error("Whiteboard", "Unable to open the whiteboard.");
}

void WhiteboardModel::onCloseWhiteboardDone(int id, QByteArray data)
{
    m_timer->stop();
}

void WhiteboardModel::onCloseWhiteboardFail(int id, QByteArray data)
{

}

void WhiteboardModel::onPushObjectDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.10.1" &&
            info["return_code"].toString() != "1.10.3")
    {
        onUpdateListFail(id, data);
        return;
    }
    WhiteboardData *item = nullptr;
    int i = 0;
    int idWhite = QVariant(obj["whiteboardId"].toString()).toInt();
    for (QVariant var : m_whiteboardList)
    {
        WhiteboardData *tmp = qobject_cast<WhiteboardData*>(var.value<WhiteboardData*>());
        if (tmp && tmp->id() == idWhite)
        {
            item = tmp;
            break;
        }
        ++i;
    }
    qDebug() << idWhite;
    qDebug() << obj["id"].toInt();
    if (item == nullptr)
        SInfoManager::GetManager()->error("Critical error", "Whiteboard not found (" + QVariant(obj["whiteboardId"].toInt()).toString() + ")");
    else
        item->addContent(obj);
    emit updatedObject(id);
}

void WhiteboardModel::onPushObjectFail(int id, QByteArray data)
{

}

void WhiteboardModel::onPullObjectDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() == "1.10.3")
        return;
    if (info["return_code"].toString() != "1.10.1")
    {
        onUpdateListFail(id, data);
        return;
    }
    WhiteboardData *item = qobject_cast<WhiteboardData*>(m_whiteboardList[m_currentItem].value<WhiteboardData*>());
    if (obj["add"].toArray().size() > 0)
        item->loadContent(obj["add"].toArray(), obj["remove"].toArray().size() == 0);
    if (obj["remove"].toArray().size() > 0)
        item->removeContents(obj["remove"].toArray());
    emit forceUpdate();

}

void WhiteboardModel::onPullObjectFail(int id, QByteArray data)
{

}

void WhiteboardModel::onRemoveObjectDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() == "1.10.3")
        return;
    if (info["return_code"].toString() != "1.10.1")
    {
        onUpdateListFail(id, data);
        return;
    }
    WhiteboardData *item = nullptr;
    int i = 0;
    int idWhite = obj["whiteboardId"].toInt();
    for (QVariant var : m_whiteboardList)
    {
        WhiteboardData *tmp = qobject_cast<WhiteboardData*>(var.value<WhiteboardData*>());
        if (tmp && tmp->id() == idWhite)
        {
            item = tmp;
            break;
        }
        ++i;
    }
    item->removeContent(obj["id"].toInt());
    emit forceUpdate();
}

void WhiteboardModel::onRemoveObjectFail(int id, QByteArray data)
{

}

void WhiteboardModel::updateWhiteboard()
{
    pullObject();
}


