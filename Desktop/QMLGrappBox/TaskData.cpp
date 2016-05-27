#include <QColor>
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

void TaskTagData::setId(int id)
{
    _Id = id;
    emit idChanged();
}

void TaskTagData::setName(QString name)
{
    _Name = name;
    emit nameChanged();
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

TaskData::TaskData()
{
    m_startDate = QDateTime::currentDateTime();
    m_dueDate = QDateTime::currentDateTime().addDays(1);
    m_color = "#c0392b";
}
