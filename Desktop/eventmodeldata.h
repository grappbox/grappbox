#ifndef EVENTMODELDATA
#define EVENTMODELDATA

#include <QObject>
#include <QJsonObject>
#include <QDebug>
#include "UserData.h"
#include "API/SDataManager.h"

class EventModelData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(int projectId READ projectId WRITE setProjectId NOTIFY projectIdChanged)
    Q_PROPERTY(UserData *creator READ creator WRITE setCreator NOTIFY creatorChanged)
    Q_PROPERTY(QString title READ title WRITE setTitle NOTIFY titleChanged)
    Q_PROPERTY(QString description READ description WRITE setDescription NOTIFY descriptionChanged)
    Q_PROPERTY(QDateTime beginDate READ beginDate WRITE setBeginDate NOTIFY beginDateChanged)
    Q_PROPERTY(QDateTime endDate READ endDate WRITE setEndDate NOTIFY endDateChanged)
    Q_PROPERTY(QDateTime createdAt READ createdAt WRITE setCreatedAt NOTIFY createdAtChanged)
    Q_PROPERTY(QDateTime editedAt READ editedAt WRITE setEditedAt NOTIFY editedAtChanged)
    Q_PROPERTY(QVariantList users READ users WRITE setUsers NOTIFY usersChanged)

public:
    EventModelData()
    {
        m_creator = nullptr;
    }

    EventModelData(QJsonObject obj)
    {
        m_creator = nullptr;
        modifyByJsonObject(obj);
    }

    void modifyByJsonObject(QJsonObject obj)
    {
        m_id = obj["id"].toInt();
        m_projectId = obj["projectId"].toInt();
        if (m_creator == nullptr)
        {
            m_creator = new UserData();
        }
        m_creator->setId(obj["creator"].toObject()["id"].toInt());
        m_creator->setFirstName(obj["creator"].toObject()["firstname"].toString());
        m_creator->setLastName(obj["creator"].toObject()["lastname"].toString());
        m_title = obj["title"].toString();
        m_description = obj["description"].toString();
        m_beginDate = JSON_TO_DATETIME(obj["beginDate"].toString());
        m_endDate = JSON_TO_DATETIME(obj["endDate"].toString());
        m_createdAt = JSON_TO_DATETIME(obj["createdAt"].toString());
        m_editedAt = JSON_TO_DATETIME(obj["editedAt"].toString());
        QList<int> usersIds;
        if (obj.contains("users"))
        {
            for (QJsonValueRef ref : obj["users"].toArray())
            {
                QJsonObject objUser = ref.toObject();
                UserData *newUser = nullptr;
                bool add = true;
                usersIds.push_back(objUser["id"].toInt());
                for (UserData *itemU : m_users)
                {
                    if (itemU->id() == objUser["id"].toInt())
                    {
                        newUser = itemU;
                        add = false;
                        break;
                    }
                }
                if (add)
                    newUser = new UserData();
                newUser->setId(objUser["id"].toInt());
                newUser->setFirstName(objUser["firstname"].toString());
                newUser->setLastName(objUser["lastname"].toString());
                if (add)
                    m_users.push_back(newUser);
            }
        }
        QList<UserData*> usersToDelete;
        for (UserData* item : m_users)
        {
            if (!usersIds.contains(item->id()))
                usersToDelete.push_back(item);
        }
        for (UserData* item : usersToDelete)
            m_users.removeAll(item);
        usersChanged(users());
        creatorChanged(creator());
        idChanged(id());
        projectIdChanged(projectId());
        titleChanged(title());
        descriptionChanged(description());
        beginDateChanged(beginDate());
        endDateChanged(endDate());
        createdAtChanged(createdAt());
        editedAtChanged(editedAt());
    }

    int id() const
    {
        return m_id;
    }
    int projectId() const
    {
        return m_projectId;
    }

    UserData * creator() const
    {
        return m_creator;
    }

    QString title() const
    {
        return m_title;
    }

    QString description() const
    {
        return m_description;
    }

    QDateTime beginDate() const
    {
        return m_beginDate;
    }

    QDateTime endDate() const
    {
        return m_endDate;
    }

    QDateTime createdAt() const
    {
        return m_createdAt;
    }

    QDateTime editedAt() const
    {
        return m_editedAt;
    }

    QVariantList users() const
    {
        QVariantList ret;
        for (UserData *item : m_users)
        {
            ret.push_back(qVariantFromValue(item));
        }
        return ret;
    }

public slots:
    void setId(int id)
    {
        if (m_id == id)
            return;

        m_id = id;
        emit idChanged(id);
    }
    void setProjectId(int projectId)
    {
        if (m_projectId == projectId)
            return;

        m_projectId = projectId;
        emit projectIdChanged(projectId);
    }

    void setCreator(UserData * creator)
    {
        if (m_creator == creator)
            return;

        m_creator = creator;
        emit creatorChanged(creator);
    }

    void setTitle(QString title)
    {
        if (m_title == title)
            return;

        m_title = title;
        emit titleChanged(title);
    }

    void setDescription(QString description)
    {
        if (m_description == description)
            return;

        m_description = description;
        emit descriptionChanged(description);
    }

    void setBeginDate(QDateTime beginDate)
    {
        if (m_beginDate == beginDate)
            return;

        m_beginDate = beginDate;
        emit beginDateChanged(beginDate);
    }

    void setEndDate(QDateTime endDate)
    {
        if (m_endDate == endDate)
            return;

        m_endDate = endDate;
        emit endDateChanged(endDate);
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

    void setUsers(QVariantList users)
    {
        m_users.clear();
        for (QVariant item : users)
        {
            UserData *itemU = qobject_cast<UserData*>(item.value<UserData*>());
            m_users.push_back(itemU);
        }
        return usersChanged(users);
    }

signals:
    void idChanged(int id);
    void projectIdChanged(int projectId);
    void creatorChanged(UserData * creator);
    void titleChanged(QString title);
    void descriptionChanged(QString description);
    void beginDateChanged(QDateTime beginDate);
    void endDateChanged(QDateTime endDate);
    void createdAtChanged(QDateTime createdAt);
    void editedAtChanged(QDateTime editedAt);
    void usersChanged(QVariantList users);

private:
    int m_id;
    int m_projectId;
    UserData * m_creator;
    QString m_title;
    QString m_description;
    QDateTime m_beginDate;
    QDateTime m_endDate;
    QDateTime m_createdAt;
    QDateTime m_editedAt;
    QList<UserData*> m_users;
};

#endif // EVENTMODELDATA

