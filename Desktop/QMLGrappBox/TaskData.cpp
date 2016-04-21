#include "TaskData.h"

//Function of task tag data
TaskTagData::TaskTagData()
{
    _Id = -1;
    _Name = "None";
}

TaskTagData::TaskTagData(int id, QString name) : QObject(nullptr)
{
    _Id = id;
    _Name = name;
}

int TaskTagData::id() const
{
    return _Id;
}

QString TaskTagData::name() const
{
    return _Name;
}

// Function of dependencies data
DependenciesData::DependenciesData()
{
    _Type = FINISH_TO_START;
    _IdTask = -1;
}

DependenciesData::DependenciesData(DependenciesType type, int id) : QObject(nullptr)
{
    _Type = type;
    _IdTask = id;
}

DependenciesData::DependenciesType DependenciesData::type() const
{
    return _Type;
}

int DependenciesData::linkedTask() const
{
    return _IdTask;
}

// Function of task data
TaskData::TaskData()
{
    _Id = -1;
    _Title = "None";
    _Description = "If you see this, their is an error on the application.";
}

TaskData::TaskData(int id, QDateTime dueDate, QDateTime startDate,
                   QDateTime finishDate, QDateTime createDate, QString title,
                   QString description, QList<UserData*> userAssigned, QList<TaskTagData*> tagAssigned,
                   QList<DependenciesData*> dependenciesAssigned) : QObject(nullptr)
{
    _Id = id;
    _DueDate = dueDate;
    _StartDate = startDate;
    _FinishDate = finishDate;
    _CreateDate = createDate;
    _Title = title;
    _Description = description;
    _UserAssigned = userAssigned;
    _TagAssigned = tagAssigned;
    _DependenciesAssigned = dependenciesAssigned;
}

int TaskData::id() const
{
    return _Id;
}

QDateTime TaskData::dueDate() const
{
    return _DueDate;
}

QDateTime TaskData::startDate() const
{
    return _StartDate;
}

QDateTime TaskData::finishDate() const
{
    return _FinishDate;
}

QDateTime TaskData::createDate() const
{
    return _CreateDate;
}

QString TaskData::title() const
{
    return _Title;
}

QString TaskData::description() const
{
    return _Description;
}

QQmlListProperty<UserData> TaskData::usersAssigned()
{
    return QQmlListProperty<UserData>(this, _UserAssigned);
}

QQmlListProperty<TaskTagData> TaskData::tagAssigned()
{
    return QQmlListProperty<TaskTagData>(this, _TagAssigned);
}

QQmlListProperty<DependenciesData> TaskData::dependenciesAssigned()
{
    return QQmlListProperty<DependenciesData>(this, _DependenciesAssigned);
}

