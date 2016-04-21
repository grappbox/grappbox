#ifndef TASKDATA_H
#define TASKDATA_H

#include <QObject>
#include <QDateTime>
#include <QQmlListProperty>
#include <QList>
#include "UserData.h"

// Class that define a tag for a task
class TaskTagData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int id READ id NOTIFY idChanged)
    Q_PROPERTY(QString name READ name NOTIFY nameChanged)

public:
    TaskTagData();
    TaskTagData(int id, QString name);

    // Getter
    int id() const;
    QString name() const;

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

    Q_PROPERTY(DependenciesType type READ type NOTIFY typeChanged)
    Q_PROPERTY(int linkedTask READ linkedTask NOTIFY linkedTaskChanged)

public:
    enum DependenciesType
    {
        FINISH_TO_START,
        START_TO_START,
        FINISH_TO_FINISH,
        START_TO_FINISH
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

private:
    DependenciesType _Type;
    int _IdTask;
};

// Class that define a task
class TaskData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int id READ id NOTIFY idChanged)
    Q_PROPERTY(QDateTime dueDate READ dueDate NOTIFY dueDateChanged)
    Q_PROPERTY(QDateTime startDate READ startDate NOTIFY startDateChanged)
    Q_PROPERTY(QDateTime finishDate READ finishDate NOTIFY finishDateChanged)
    Q_PROPERTY(QDateTime createDate READ createDate NOTIFY createDateChanged)
    Q_PROPERTY(QString title READ title NOTIFY titleChanged)
    Q_PROPERTY(QString description READ description NOTIFY descriptionChanged)
    Q_PROPERTY(QQmlListProperty<UserData> usersAssigned READ usersAssigned NOTIFY usersAssignedChanged)
    Q_PROPERTY(QQmlListProperty<TaskTagData> tagAssigned READ tagAssigned NOTIFY tagAssignedChanged)
    Q_PROPERTY(QQmlListProperty<DependenciesData> dependenciesAssigned READ dependenciesAssigned NOTIFY dependenciesAssignedChanged)

public:
    TaskData();
    TaskData(int id, QDateTime dueDate, QDateTime startDate,
             QDateTime finishDate, QDateTime createDate, QString title,
             QString description, QList<UserData*> userAssigned, QList<TaskTagData*> tagAssigned,
             QList<DependenciesData*> dependenciesAssigned);

    // Getter
    int id() const;
    QDateTime dueDate() const;
    QDateTime startDate() const;
    QDateTime finishDate() const;
    QDateTime createDate() const;
    QString title() const;
    QString description() const;
    QQmlListProperty<UserData> usersAssigned();
    QQmlListProperty<TaskTagData> tagAssigned();
    QQmlListProperty<DependenciesData> dependenciesAssigned();

signals:
    void idChanged();
    void dueDateChanged();
    void startDateChanged();
    void finishDateChanged();
    void createDateChanged();
    void titleChanged();
    void descriptionChanged();
    void usersAssignedChanged();
    void tagAssignedChanged();
    void dependenciesAssignedChanged();

public slots:

private:
    int _Id;
    QDateTime _DueDate;
    QDateTime _StartDate;
    QDateTime _FinishDate;
    QDateTime _CreateDate;
    QString _Title;
    QString _Description;
    QList<UserData*> _UserAssigned;
    QList<TaskTagData*> _TagAssigned;
    QList<DependenciesData*> _DependenciesAssigned;
};

#endif // TASKDATA_H
