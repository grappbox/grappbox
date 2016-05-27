#ifndef GANTTMODEL_H
#define GANTTMODEL_H

#include <QObject>
#include <QList>
#include <QQmlListProperty>
#include <QStringListModel>
#include "TaskData.h"
#include "API/SDataManager.h"

class GanttModel : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QVariantList tasks READ tasks NOTIFY tasksChanged)
    Q_PROPERTY(bool isLoading READ isLoading NOTIFY isLoadingChanged)
    Q_PROPERTY(QStringList taskName READ taskName NOTIFY taskNameChanged)
    Q_PROPERTY(QVariantList taskTags READ taskTags NOTIFY taskTagsChanged)

public:
    explicit GanttModel(QObject *parent = 0);

    // Getter
    QVariantList tasks();
    bool isLoading();
    QStringList taskName(int ignoreId = -1);
    QVariantList taskTags();
    QStringListModel &taskNameModel(int ignoreId = -1);

    Q_INVOKABLE void loadTasks();
    Q_INVOKABLE void loadTaskTag();
    Q_INVOKABLE int idByTaskNameArray(int index) const;
    Q_INVOKABLE void addTag(QString name);
    Q_INVOKABLE void removeTag(int id);

signals:
    void tasksChanged();
    void isLoadingChanged();
    void taskTagsChanged();
    void error(QString title, QString message);

    void taskNameChanged();

public slots:
    void OnLoadTaskDone(int id, QByteArray data);
    void OnLoadTaskFail(int id, QByteArray data);
    void OnLoadTaskTagDone(int id, QByteArray data);
    void OnLoadTaskTagFail(int id, QByteArray data);
    void OnAddTagDone(int id, QByteArray data);
    void OnAddTagFail(int id, QByteArray data);
    void OnRemoveTagDone(int id, QByteArray data);
    void OnRemoveTagFail(int id, QByteArray data);

private:
    QList<TaskData*> _Tasks;
    QList<TaskTagData*> _TaskTags;
    bool _IsLoading;
    QStringListModel _Model;
    QStringList _ListName;
};

#endif // GANTTMODEL_H
