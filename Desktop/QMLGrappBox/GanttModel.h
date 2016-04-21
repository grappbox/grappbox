#ifndef GANTTMODEL_H
#define GANTTMODEL_H

#include <QObject>
#include <QList>
#include <QQmlListProperty>
#include "TaskData.h"

class GanttModel : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QVariantList tasks READ tasks NOTIFY tasksChanged)

public:
    explicit GanttModel(QObject *parent = 0);

    // Getter
    QVariantList tasks();

signals:
    void tasksChanged();

public slots:

private:
    QList<TaskData*> _Tasks;
};

#endif // GANTTMODEL_H
