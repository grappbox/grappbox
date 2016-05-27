#ifndef TASKDATA_H
#define TASKDATA_H

#include <QObject>
#include <QDateTime>
#include <QQmlListProperty>
#include <QList>
#include <QDebug>
#include "UserData.h"

// Class that define a tag for a task
class TaskTagData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QString name READ name WRITE setName NOTIFY nameChanged)

public:
    TaskTagData();
    TaskTagData(int id, QString name);

    // Getter
    int id() const;
    QString name() const;

    // Setter
    void setId(int id);
    void setName(QString name);

signals:
    void idChanged();
    void nameChanged();

public slots:

private:
    int _Id;
    QString _Name;
};

// Class that define a dependencies for a task
class DependenciesData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(DependenciesType type READ type WRITE setType NOTIFY typeChanged)
    Q_PROPERTY(int linkedTask READ linkedTask WRITE setLinkedTask NOTIFY linkedTaskChanged)
    Q_ENUMS(DependenciesType)


public:
    enum DependenciesType
    {
        FINISH_TO_START = 0,
        START_TO_START = 1,
        FINISH_TO_FINISH = 2,
        START_TO_FINISH = 3
    };

public:
    DependenciesData();
    DependenciesData(DependenciesType type, int id);

    // Getter
    DependenciesType type() const;
    int linkedTask() const;

signals:
    void typeChanged();
    void linkedTaskChanged();

public slots:

    void setType(DependenciesType type)
    {
        if (_Type == type)
            return;

        _Type = type;
        emit typeChanged();
    }

    void setLinkedTask(int linkedTask)
    {
        if (_IdTask == linkedTask)
            return;

        _IdTask = linkedTask;
        emit linkedTaskChanged();
    }

private:
    DependenciesType _Type;
    int _IdTask;
};

class TaskRessources : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int idUser READ idUser WRITE setIdUser NOTIFY idUserChanged)
    Q_PROPERTY(int ressources READ ressources WRITE setRessources NOTIFY ressourcesChanged)

public:
    TaskRessources()
    {
        m_idUser = 0;
        m_ressources = 100;
    }

    int idUser() const
    {
        return m_idUser;
    }

    int ressources() const
    {
        return m_ressources;
    }

signals:

    void idUserChanged(int idUser);

    void ressourcesChanged(int ressources);

public slots:

void setIdUser(int idUser)
{
    if (m_idUser == idUser)
        return;

    m_idUser = idUser;
    emit idUserChanged(idUser);
}

void setRessources(int ressources)
{
    if (m_ressources == ressources)
        return;

    m_ressources = ressources;
    emit ressourcesChanged(ressources);
}

private:

int m_idUser;
int m_ressources;
};

// Class that define a task
class TaskData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(bool isMilestone READ isMilestone WRITE setIsMilestone NOTIFY isMilestoneChanged)
    Q_PROPERTY(QDateTime dueDate READ dueDate WRITE setDueDate NOTIFY dueDateChanged)
    Q_PROPERTY(QDateTime startDate READ startDate WRITE setStartDate NOTIFY startDateChanged)
    Q_PROPERTY(QDateTime finishDate READ finishDate WRITE setFinishDate NOTIFY finishDateChanged)
    Q_PROPERTY(QDateTime createDate READ createDate WRITE setCreateDate NOTIFY createDateChanged)
    Q_PROPERTY(QString title READ title WRITE setTitle NOTIFY titleChanged)
    Q_PROPERTY(QString description READ description WRITE setDescription NOTIFY descriptionChanged)
    Q_PROPERTY(QVariantList tagAssigned READ tagAssigned WRITE setTagAssigned NOTIFY tagAssignedChanged)
    Q_PROPERTY(QVariantList dependenciesAssigned WRITE setDependenceiesAssigned READ dependenciesAssigned NOTIFY dependenciesAssignedChanged)
    Q_PROPERTY(QVariantList usersRessources READ usersRessources WRITE setUserRessources NOTIFY userRessourcesChanged)
    Q_PROPERTY(float progression READ progression WRITE setProgression WRITE setProgression NOTIFY progressionChanged)
    Q_PROPERTY(QString color READ color WRITE setColor NOTIFY colorChanged)

public:
    TaskData();

    Q_INVOKABLE void replaceUser(UserData *user, UserData *newUser)
    {
        if (user == newUser)
            return;
        int index = -1;
        for (UserData *userTmp : m_usersAssigned)
        {
            if (user == userTmp)
            {
                index = m_usersAssigned.indexOf(userTmp);
                break;
            }
        }
        qDebug() << "Replace user at index " << index << " by user : " << newUser->firstName();
        m_usersAssigned.replace(index, newUser);
        for (UserData *userTmp : m_usersAssigned)
        {
            qDebug() << "User ok : " << userTmp->firstName();
        }
        emit usersAssignedChanged(usersAssigned());
    }

    Q_INVOKABLE bool hasDoubleUser()
    {
        for (UserData *userTmp : m_usersAssigned)
        {
            int number = 0;
            for (UserData *userTmp2 : m_usersAssigned)
            {
                if (userTmp == userTmp2)
                {
                    number++;
                    if (number == 2)
                        return true;
                }
            }
        }
        return false;
    }

    Q_INVOKABLE void addTag(TaskTagData *data)
    {
        for (TaskTagData *task : m_tagAssigned)
        {
            if (task == data)
                return;
        }
        m_tagAssigned.push_back(data);
        emit tagAssignedChanged(tagAssigned());
    }

    Q_INVOKABLE void removeTag(TaskTagData *data)
    {
        for (TaskTagData *task : m_tagAssigned)
        {
            if (task == data)
            {
                m_tagAssigned.removeAll(task);
                emit tagAssignedChanged(tagAssigned());
                return;
            }
        }
    }

    Q_INVOKABLE bool isOnTask(TaskTagData *data)
    {
        for (TaskTagData *task : m_tagAssigned)
        {
            if (task == data)
            {
                qDebug() << "Tag " << task->name() << " is on task.";
                return true;
            }
        }
        return false;
    }

    int id() const
    {
        return m_id;
    }

    bool isMilestone() const
    {
        return m_isMilestone;
    }

    QDateTime dueDate() const
    {
        return m_dueDate;
    }

    QDateTime startDate() const
    {
        return m_startDate;
    }

    QDateTime finishDate() const
    {
        return m_finishDate;
    }

    QDateTime createDate() const
    {
        return m_createDate;
    }

    QString title() const
    {
        return m_title;
    }

    QString description() const
    {
        return m_description;
    }

    QVariantList usersAssigned() const
    {
        QVariantList list;
        for (UserData *data : m_usersAssigned)
        {
            list.append(qVariantFromValue(data));
        }
        return list;
    }

    QVariantList tagAssigned() const
    {
        QVariantList list;
        for (TaskTagData *data : m_tagAssigned)
        {
            list.append(qVariantFromValue(data));
        }
        return list;
    }

    QVariantList dependenciesAssigned() const
    {
        QVariantList list;
        for (DependenciesData *data : m_dependenciesAssigned)
        {
            list.append(qVariantFromValue(data));
        }
        return list;
    }

    float progression() const
    {
        return m_progression;
    }

    QString color() const
    {
        return m_color;
    }

    QVariantList usersRessources() const
    {
        QVariantList list;
        for (TaskRessources *data : m_usersRessources)
        {
            list.append(qVariantFromValue(data));
        }
        return list;
    }

    void setId(int id)
    {
        if (m_id == id)
            return;

        m_id = id;
        emit idChanged(id);
    }

    void setIsMilestone(bool isMilestone)
    {
        if (m_isMilestone == isMilestone)
            return;

        m_isMilestone = isMilestone;
        emit isMilestoneChanged(isMilestone);
    }

    void setDueDate(QDateTime dueDate)
    {
        if (m_dueDate == dueDate)
            return;

        m_dueDate = dueDate;
        emit dueDateChanged(dueDate);
    }

    void setStartDate(QDateTime startDate)
    {
        if (m_startDate == startDate)
            return;

        m_startDate = startDate;
        emit startDateChanged(startDate);
    }

    void setFinishDate(QDateTime finishDate)
    {
        if (m_finishDate == finishDate)
            return;

        m_finishDate = finishDate;
        emit finishDateChanged(finishDate);
    }

    void setCreateDate(QDateTime createDate)
    {
        if (m_createDate == createDate)
            return;

        m_createDate = createDate;
        emit createDateChanged(createDate);
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

    void setUserAssigned(QVariantList usersAssigned)
    {
        m_usersAssigned.clear();
        for (QVariant var : usersAssigned)
        {
            UserData *obj = qobject_cast<UserData*>(var.value<UserData*>());
            if (obj != nullptr)
                m_usersAssigned.push_back(obj);
        }
        emit usersAssignedChanged(usersAssigned);
    }

    void setTagAssigned(QVariantList tagAssigned)
    {
        m_tagAssigned.clear();
        for (QVariant var : tagAssigned)
        {
            TaskTagData *obj = qobject_cast<TaskTagData*>(var.value<TaskData*>());
            if (obj != nullptr)
                m_tagAssigned.push_back(obj);
        }
        emit tagAssignedChanged(tagAssigned);
    }

    void setDependenceiesAssigned(QVariantList dependenciesAssigned)
    {
        m_dependenciesAssigned.clear();
        for (QVariant var : dependenciesAssigned)
        {
            DependenciesData *obj = qobject_cast<DependenciesData*>(var.value<DependenciesData*>());
            if (obj != nullptr)
                m_dependenciesAssigned.push_back(obj);
        }
        emit dependenciesAssignedChanged(dependenciesAssigned);
    }

    void setProgression(float progression)
    {
        if (m_progression == progression)
            return;

        m_progression = progression;
        emit progressionChanged(progression);
    }

    void setColor(QString color)
    {
        if (m_color == color)
            return;

        m_color = color;
        emit colorChanged(color);
    }

    void setUserRessources(QVariantList usersRessources)
    {
        m_usersRessources.clear();
        for (QVariant var : usersRessources)
        {
            TaskRessources *obj = qobject_cast<TaskRessources*>(var.value<TaskRessources*>());
            if (obj != nullptr)
                m_usersRessources.push_back(obj);
        }
        //emit userRessourcesChanged(usersRessources());
    }

signals:


    void idChanged(int id);

    void isMilestoneChanged(bool isMilestone);

    void dueDateChanged(QDateTime dueDate);

    void startDateChanged(QDateTime startDate);

    void finishDateChanged(QDateTime finishDate);

    void createDateChanged(QDateTime createDate);

    void titleChanged(QString title);

    void descriptionChanged(QString description);

    void usersAssignedChanged(QVariantList usersAssigned);

    void tagAssignedChanged(QVariantList tagAssigned);

    void dependenciesAssignedChanged(QVariantList dependenciesAssigned);

    void progressionChanged(float progression);

    void colorChanged(QString color);

    void userRessourcesChanged(QVariantList usersRessources);

public slots:


private:

    int m_id;
    bool m_isMilestone;
    QDateTime m_dueDate;
    QDateTime m_startDate;
    QDateTime m_finishDate;
    QDateTime m_createDate;
    QString m_title;
    QString m_description;
    QList<UserData*> m_usersAssigned;
    QList<TaskTagData*> m_tagAssigned;
    QList<DependenciesData*> m_dependenciesAssigned;
    float m_progression;

    QString m_color;
    QList<TaskRessources*> m_usersRessources;
};

#endif // TASKDATA_H
