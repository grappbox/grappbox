#include <QDebug>
#include "GanttModel.h"

GanttModel::GanttModel(QObject *parent) : QObject(parent)
{
    QDateTime dueDate(QDate::currentDate(), QTime(5, 6));
    QDateTime startDate = dueDate.addDays(-3);
    QDateTime createDate = dueDate.addDays(-8);
    TaskData *d = new TaskData(1, dueDate, startDate, QDateTime(), createDate, "Tash #1", "Description", QList<UserData*>(), QList<TaskTagData*>(), QList<DependenciesData*>());
    _Tasks.push_back(d);

    dueDate = dueDate.addDays(5);
    startDate = dueDate.addDays(-5);
    createDate = dueDate.addDays(-8);

    TaskData *d2 = new TaskData(1, dueDate, startDate, QDateTime(), createDate, "Tash #2", "Description", QList<UserData*>(), QList<TaskTagData*>(), QList<DependenciesData*>());
    _Tasks.push_back(d2);

    dueDate = dueDate.addDays(-15);
    startDate = dueDate.addDays(-7);
    createDate = dueDate.addDays(-8);

    TaskData *d3 = new TaskData(1, dueDate, startDate, QDateTime(), createDate, "Tash #3", "Description", QList<UserData*>(), QList<TaskTagData*>(), QList<DependenciesData*>());
    _Tasks.push_back(d3);

    dueDate = dueDate.addDays(20);
    startDate = dueDate.addDays(-10);
    createDate = dueDate.addDays(-8);

    TaskData *d4 = new TaskData(1, dueDate, startDate, QDateTime(), createDate, "Tash #4", "Description", QList<UserData*>(), QList<TaskTagData*>(), QList<DependenciesData*>());
    _Tasks.push_back(d4);

    dueDate = dueDate.addDays(3);
    startDate = dueDate.addDays(-8);
    createDate = dueDate.addDays(-8);

    TaskData *d5 = new TaskData(1, dueDate, startDate, QDateTime(), createDate, "Tash #5", "Description", QList<UserData*>(), QList<TaskTagData*>(), QList<DependenciesData*>());
    _Tasks.push_back(d5);
}

QVariantList GanttModel::tasks()
{
    QVariantList list;
    for (TaskData *data : _Tasks)
    {
        list.append(qVariantFromValue(data));
    }
    return list;
}
