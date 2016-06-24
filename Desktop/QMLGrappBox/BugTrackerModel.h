#ifndef BUGTRACKERMODEL_H
#define BUGTRACKERMODEL_H

#include "API/SDataManager.h"
#include <QList>
#include <QMap>
#include <QObject>
#include <QByteArray>
#include <QJsonObject>

class BugTrackerComment : public QObject
{
    Q_OBJECT
    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(int parentId READ parentId WRITE setParentId NOTIFY parentIdChanged)
    Q_PROPERTY(QString message READ message WRITE setMessage NOTIFY messageChanged)
    Q_PROPERTY(UserData* user READ user WRITE setUser NOTIFY userChanged)
    Q_PROPERTY(QDateTime createdAt READ createdAt WRITE setCreatedAt NOTIFY createdAtChanged)
    Q_PROPERTY(QDateTime editedAt READ editedAt WRITE setEditedAt NOTIFY editedAtChanged)

public:

    BugTrackerComment()
    {
        m_user = nullptr;
    }

    BugTrackerComment(QJsonObject obj)
    {
        m_user = nullptr;
        modifyByJsonObject(obj);
    }

    void modifyByJsonObject(QJsonObject obj)
    {
        m_id = obj["id"].toInt();
        m_parentId = obj["parentId"].toInt();
        m_message = obj["description"].toString();
        m_createdAt = JSON_TO_DATETIME(obj["createdAt"].toString());
        m_editedAt = JSON_TO_DATETIME(obj["editedAt"].toString());
        if (m_user == nullptr)
            m_user = new UserData();
        m_user->setId(obj["creator"].toObject()["id"].toInt());
        m_user->setFirstName(obj["creator"].toObject()["fullname"].toString());
        m_user->setLastName(m_user->firstName());
    }

    int id() const
    {
        return m_id;
    }

    QString message() const
    {
        return m_message;
    }

    UserData* user() const
    {
        return m_user;
    }

    QDateTime createdAt() const
    {
        return m_createdAt;
    }

    QDateTime editedAt() const
    {
        return m_editedAt;
    }

    int parentId() const
    {
        return m_parentId;
    }

signals:

    void idChanged(int id);

    void messageChanged(QString message);

    void userChanged(UserData* user);

    void createdAtChanged(QDateTime createdAt);

    void editedAtChanged(QDateTime editedAt);

    void parentIdChanged(int parentId);

public slots:

void setId(int id)
{
    if (m_id == id)
        return;

    m_id = id;
    emit idChanged(id);
}

void setMessage(QString message)
{
    if (m_message == message)
        return;

    m_message = message;
    emit messageChanged(message);
}

void setUser(UserData* user)
{
    if (m_user == user)
        return;

    m_user = user;
    emit userChanged(user);
}

void setCreatedAt(QDateTime createdAt)
{
    if (m_createdAt == createdAt)
        return;

    m_createdAt = createdAt;
    emit createdAtChanged(createdAt);
}

void setEditedAt(QDateTime editedAt)
{
    if (m_editedAt == editedAt)
        return;

    m_editedAt = editedAt;
    emit editedAtChanged(editedAt);
}

void setParentId(int parentId)
{
    if (m_parentId == parentId)
        return;

    m_parentId = parentId;
    emit parentIdChanged(parentId);
}

private:

int m_id;
QString m_message;
UserData* m_user;
QDateTime m_createdAt;
QDateTime m_editedAt;
int m_parentId;
};

class BugTrackerTags : public QObject
{
    Q_OBJECT
    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QString name READ name WRITE setName NOTIFY nameChanged)

public:

    int id() const
    {
        return m_id;
    }

    QString name() const
    {
        return m_name;
    }

signals:

    void idChanged(int id);

    void nameChanged(QString name);

public slots:

    void setId(int id)
    {
        if (m_id == id)
            return;

        m_id = id;
        emit idChanged(id);
    }

    void setName(QString name)
    {
        if (m_name == name)
            return;

        m_name = name;
        emit nameChanged(name);
    }

private:
    int m_id;
    QString m_name;
};

class BugTrackerTicketData : public QObject
{
    Q_OBJECT
    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QString title READ title WRITE setTitle NOTIFY titleChanged)
    Q_PROPERTY(QString message READ message WRITE setMessage NOTIFY messageChanged)
    Q_PROPERTY(UserData* creator READ creator WRITE setCreator NOTIFY creatorChanged)
    Q_PROPERTY(QVariantList tags READ tags WRITE setTags NOTIFY tagsChanged)
    Q_PROPERTY(QVariantList users READ users WRITE setUsers NOTIFY usersChanged)
    Q_PROPERTY(QDateTime createDate READ createDate WRITE setCreateDate NOTIFY createDateChanged)
    Q_PROPERTY(QDateTime editDate READ editDate WRITE setEditDate NOTIFY editDateChanged)
    Q_PROPERTY(QDateTime closeDate READ closeDate WRITE setCloseDate NOTIFY closeDateChanged)
    Q_PROPERTY(QVariantList comments READ comments WRITE setComments NOTIFY commentsChanged)

public:
    BugTrackerTicketData()
    {
        m_creator = new UserData();
    }

    BugTrackerTicketData(QJsonObject obj)
    {
        m_creator = new UserData();
        modifyByJsonObject(obj);
    }

    void modifyByJsonObject(QJsonObject obj)
    {
        m_id = obj["id"].toInt();
        m_creator->setId(obj["creator"].toObject()["id"].toInt());
        m_creator->setFirstName(obj["creator"].toObject()["fullname"].toString());
        m_creator->setLastName(m_creator->firstName());
        m_title = obj["title"].toString();
        m_message = obj["description"].toString();
        m_createDate = JSON_TO_DATETIME(obj["createdAt"].toObject()["date"].toString());
        if (obj["editedAt"].isNull())
            m_editDate = m_createDate;
        else
            m_editDate = JSON_TO_DATETIME(obj["editedAt"].toObject()["date"].toString());
        if (obj["deletedAt"].isNull())
            m_closeDate = m_editDate;
        else
            m_closeDate = JSON_TO_DATETIME(obj["deletedAt"].toObject()["date"].toString());
        m_tags.clear();
        for (QJsonValueRef tagObj : obj["tags"].toArray())
            m_tags.push_back(tagObj.toObject()["id"].toInt());
        for (QJsonValueRef userObj : obj["users"].toArray())
            m_users.push_back(userObj.toObject()["id"].toInt());
    }

    int id() const
    {
        return m_id;
    }

    QString title() const
    {
        return m_title;
    }

    QString message() const
    {
        return m_message;
    }

    UserData *creator() const
    {
        return m_creator;
    }

    QVariantList tags() const
    {
        QVariantList ret;
        for (int item : m_tags)
            ret.push_back(item);
        return ret;
    }

    QVariantList users() const
    {
        QVariantList ret;
        for (int item : m_users)
            ret.push_back(item);
        return ret;
    }

    QDateTime createDate() const
    {
        return m_createDate;
    }

    QDateTime editDate() const
    {
        return m_editDate;
    }

    QDateTime closeDate() const
    {
        return m_closeDate;
    }

    QVariantList comments() const
    {
        QVariantList ret;
        for (BugTrackerComment* item : m_comments)
            ret.push_back(qVariantFromValue(item));
        return ret;
    }

    QList<BugTrackerComment*> &realListComment()
    {
        return m_comments;
    }

signals:

    void idChanged(int id);

    void titleChanged(QString title);

    void messageChanged(QString message);

    void creatorChanged(UserData *creator);

    void tagsChanged(QVariantList tags);

    void usersChanged(QVariantList users);

    void createDateChanged(QDateTime createDate);

    void editDateChanged(QDateTime editDate);

    void closeDateChanged(QDateTime closeDate);

    void commentsChanged(QVariantList comments);

public slots:

    void setId(int id)
    {
        if (m_id == id)
            return;

        m_id = id;
        emit idChanged(id);
    }

    void setTitle(QString title)
    {
        if (m_title == title)
            return;

        m_title = title;
        emit titleChanged(title);
    }

    void setMessage(QString message)
    {
        if (m_message == message)
            return;

        m_message = message;
        emit messageChanged(message);
    }

    void setCreator(UserData *creator)
    {
        if (m_creator == creator)
            return;

        m_creator = creator;
        emit creatorChanged(creator);
    }

    void setTags(QVariantList tags)
    {
        m_tags.clear();
        for (QVariant var : tags)
        {
            m_tags.push_back(var.toInt());
        }

        emit tagsChanged(tags);
    }

    void setUsers(QVariantList users)
    {
        m_users.clear();
        for (QVariant var : users)
        {
            m_users.push_back(var.toInt());
        }

        emit usersChanged(users);
    }

    void setCreateDate(QDateTime createDate)
    {
        if (m_createDate == createDate)
            return;

        m_createDate = createDate;
        emit createDateChanged(createDate);
    }

    void setEditDate(QDateTime editDate)
    {
        if (m_editDate == editDate)
            return;

        m_editDate = editDate;
        emit editDateChanged(editDate);
    }

    void setCloseDate(QDateTime closeDate)
    {
        if (m_closeDate == closeDate)
            return;

        m_closeDate = closeDate;
        emit closeDateChanged(closeDate);
    }

    void setComments(QVariantList comments)
    {
        m_comments.clear();
        for (QVariant var : comments)
        {
            m_comments.push_back(qobject_cast<BugTrackerComment*>(var.value<BugTrackerComment*>()));
        }

        emit commentsChanged(comments);
    }

private:

    int m_id;
    QString m_title;
    QString m_message;
    UserData *m_creator;
    QList<int> m_tags;
    QList<int> m_users;
    QDateTime m_createDate;
    QDateTime m_editDate;
    QDateTime m_closeDate;
    QList<BugTrackerComment*> m_comments;
};

class BugTrackerModel : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QVariantList closedTickets READ closedTickets WRITE setClosedTickets NOTIFY closedTicketsChanged)
    Q_PROPERTY(QVariantList openTickets READ openTickets WRITE setOpenTickets NOTIFY openTicketsChanged)
    Q_PROPERTY(QVariantList yoursTickets READ yoursTickets WRITE setYoursTickets NOTIFY yoursTicketsChanged)
    Q_PROPERTY(QVariantList tags READ tags WRITE setTags NOTIFY tagsChanged)

public:
    BugTrackerModel();

    Q_INVOKABLE void loadTags();

    Q_INVOKABLE void loadClosedTickets();
    Q_INVOKABLE void loadOpenTickets();
    Q_INVOKABLE void loadYoursTickets();

    Q_INVOKABLE void loadCommentTicket(int id);

    Q_INVOKABLE void addTicket(QString title, QString message, QVariantList users, QVariantList tags);
    Q_INVOKABLE void modifyTicket(/*Add fields here...*/);
    Q_INVOKABLE void addUsersToTicket(int idTicket, int idUsers);
    Q_INVOKABLE void removeUsersToTicket(int idTicket, int idUsers);
    Q_INVOKABLE void addTagsToTicket(int idTicket, int idTag);
    Q_INVOKABLE void removeTagsToTicket(int idTicket, int idTag);
    Q_INVOKABLE void createAndAddTagsToTicket(int idTicket, QString tag);
    Q_INVOKABLE void closeTicket(int idTicket);

    Q_INVOKABLE void addComment(QString comment);
    Q_INVOKABLE void removeComment(int idComment);
    Q_INVOKABLE void modifyComment(QString comment);

    QVariantList closedTickets() const
    {
        QVariantList ret;
        for (BugTrackerTicketData *item : m_closedTickets)
        {
            ret.push_back(qVariantFromValue(item));
        }
        return ret;
    }

    QVariantList openTickets() const
    {
        QVariantList ret;
        for (BugTrackerTicketData *item : m_openTickets)
        {
            ret.push_back(qVariantFromValue(item));
        }
        return ret;
    }

    QVariantList yoursTickets() const
    {
        QVariantList ret;
        for (BugTrackerTicketData *item : m_yoursTickets)
        {
            ret.push_back(qVariantFromValue(item));
        }
        return ret;
    }

    QVariantList tags() const
    {
        QVariantList ret;
        for (BugTrackerTags *item : m_tags)
        {
            ret.push_back(qVariantFromValue(item));
        }
        return ret;
    }

signals:

    void closedTicketsChanged(QVariantList closedTickets);

    void openTicketsChanged(QVariantList openTickets);

    void yoursTicketsChanged(QVariantList yoursTickets);

    void tagsChanged(QVariantList tags);

public slots:
    void onLoadClosedTicketDone(int id, QByteArray data);
    void onLoadClosedTicketFail(int id, QByteArray data);
    void onLoadOpenTicketDone(int id, QByteArray data);
    void onLoadOpenTicketFail(int id, QByteArray data);
    void onLoadYoursTicketDone(int id, QByteArray data);
    void onLoadYoursTicketFail(int id, QByteArray data);
    void onLoadCommentTicketDone(int id, QByteArray data);
    void onLoadCommentTicketFail(int id, QByteArray data);
    void onLoadTagsDone(int id, QByteArray data);
    void onLoadTagsFail(int id, QByteArray data);
    void onAddTagDone(int id, QByteArray data);
    void onAddTagFail(int id, QByteArray data);
    void onAddTicketDone(int id, QByteArray data);
    void onAddTicketFail(int id, QByteArray data);

    void setClosedTickets(QVariantList closedTickets)
    {
        m_closedTickets.clear();
        for (QVariant var : closedTickets)
        {
            m_closedTickets.push_back(qobject_cast<BugTrackerTicketData*>(var.value<BugTrackerTicketData*>()));
        }
        emit closedTicketsChanged(closedTickets);
    }

    void setOpenTickets(QVariantList openTickets)
    {
        m_openTickets.clear();
        for (QVariant var : openTickets)
        {
            m_openTickets.push_back(qobject_cast<BugTrackerTicketData*>(var.value<BugTrackerTicketData*>()));
        }
        emit openTicketsChanged(openTickets);
    }

    void setYoursTickets(QVariantList yoursTickets)
    {
        m_yoursTickets.clear();
        for (QVariant var : yoursTickets)
        {
            m_yoursTickets.push_back(qobject_cast<BugTrackerTicketData*>(var.value<BugTrackerTicketData*>()));
        }
        emit yoursTicketsChanged(yoursTickets);
    }

    void setTags(QVariantList tags)
    {
        m_tags.clear();
        for (QVariant var : tags)
        {
            m_tags.push_back(qobject_cast<BugTrackerTags*>(var.value<BugTrackerTags*>()));
        }
        emit tagsChanged(tags);
    }

private:

    QList<BugTrackerTicketData*> m_closedTickets;
    QList<BugTrackerTicketData*> m_openTickets;
    QList<BugTrackerTicketData*> m_yoursTickets;
    QList<BugTrackerTags*> m_tags;
    QMap<int, int> m_loadingComment;
    QMap<int, int> m_addingComment;
    QMap<int, int> m_addingTags;
};

#endif // BUGTRACKERMODEL_H
