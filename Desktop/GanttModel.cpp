#include <QDebug>
#include "GanttModel.h"

GanttModel::GanttModel(QObject *parent) : QObject(parent)
{
    _lastTagAdded = -1;
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

bool GanttModel::isLoading()
{
    return _IsLoading;
}

QVariantList GanttModel::taskTags()
{
    QVariantList list;
    for (TaskTagData *data : _TaskTags)
    {
        list.append(qVariantFromValue(data));
    }
    return list;
}

void GanttModel::loadTasks()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnLoadTaskDone");
        SET_ON_FAIL("OnLoadTaskFail");
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_GANTT, API::GR_LIST_TASK);
    }
    END_REQUEST;
    _IsLoading = true;
    emit isLoadingChanged();
}

void GanttModel::loadTaskTag()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnLoadTaskTagDone");
        SET_ON_FAIL("OnLoadTaskTagFail");
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_GANTT, API::GR_LIST_TASK_TAG);
    }
    END_REQUEST;
    _IsLoading = true;
    emit isLoadingChanged();
}

// PROBLEME
void GanttModel::loadTask(int id)
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnLoadTaskTagDone");
        SET_ON_FAIL("OnLoadTaskTagFail");
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(_Tasks[id]->id());
        GET(API::DP_GANTT, API::GR_TASK);
    }
    END_REQUEST;
}

QStringListModel &GanttModel::taskNameModel(int ignoreId)
{
    _Model.setStringList(taskName(ignoreId));
    return _Model;
}

QStringList GanttModel::taskName(int ignoreId)
{
    _ListName.clear();
    _ListName.push_back("No task selected.");
    for (TaskData *task : _Tasks)
    {
        if (ignoreId != task->id())
            _ListName.push_back(task->title());
    }
    return _ListName;
}

int GanttModel::idByTaskNameArray(int index) const
{
    if (index == 0)
        return -1;
    index--;
    for (TaskData *task : _Tasks)
    {
        if (index == 0)
            return task->id();
        index--;
    }
    return -1;
}

void GanttModel::addTag(QString name, QString color)
{
    BEGIN_REQUEST;
    {
        EPURE_WARNING_INDEX
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnAddTagDone");
        SET_ON_FAIL("OnAddTagFail");
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_FIELD("projectId", PROJECT);
        ADD_FIELD("name", name);
        ADD_FIELD("color", color.split("#")[1]);
        POST(API::DP_GANTT, API::PR_ADD_TAG_TASK);
    }
    END_REQUEST;
}

void GanttModel::removeTag(int id)
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnRemoveTagDone");
        SET_ON_FAIL("OnRemoveTagFail");
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(id);
        DELETE_REQ(API::DP_GANTT, API::DR_TASK_TAG);
    }
    END_REQUEST;
}

void GanttModel::addTask(QString title,
                         QString description,
                         bool isMilestone,
                         int progression,
                         QDateTime startDate,
                         QDateTime endDate,
                         QVariantList users,
                         QVariantList dependencies,
                         QVariantList containedTasks,
                         QVariantList tags)
{
    BEGIN_REQUEST_ADV(this, "OnAddTaskDone", "OnAddTaskFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_FIELD("projectId", PROJECT);
        ADD_FIELD("title", title);
        ADD_FIELD("description", description);
        ADD_FIELD("is_milestone", isMilestone);
        ADD_FIELD("is_container", containedTasks.length() > 0);
        ADD_FIELD("started_at", startDate.toString("yyyy-MM-dd hh:mm:ss"));
        ADD_FIELD("due_date", endDate.toString("yyyy-MM-dd hh:mm:ss"));
        ADD_FIELD("advance", progression);
        POST(API::DP_TASK, API::PR_CREATE_TASK);
    }
    END_REQUEST;
}

void GanttModel::OnLoadTaskDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.12.1")
    {
        OnLoadTaskFail(id, data);
        return;
    }
    _Tasks.clear();
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject task = ref.toObject();
        TaskData *currentTask = nullptr;
        for (TaskData *var : _Tasks)
        {
            if (var && var->id() == task["id"].toInt())
            {
                currentTask = var;
                break;
            }
        }
        if (currentTask)
            currentTask->modifyDataByJson(task);
        else
            _Tasks.push_back(new TaskData(task));
    }
    emit taskNameChanged();
    emit tasksChanged();
}

void GanttModel::OnLoadTaskFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    emit error("Project task error", "Unable to retrieve task for your project. Please try again later.");
}

void GanttModel::OnLoadTaskTagDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.12.1")
    {
        OnLoadTaskFail(id, data);
        return;
    }
    QList<int> idToKeep;
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject taskData = ref.toObject();
        TaskTagData *data = nullptr;
        idToKeep.push_back(taskData["id"].toInt());
        for (TaskTagData *tmpTag : _TaskTags)
        {
            if (tmpTag->id() == taskData["id"].toInt())
            {
                tmpTag->setName(taskData["name"].toString());
                tmpTag->setColor("#" + taskData["color"].toString());
                data = tmpTag;
                break;
            }
        }
        if (data == nullptr)
        {
            data = new TaskTagData(taskData["id"].toInt(), taskData["name"].toString());
            data->setColor("#" + taskData["color"].toString());
            _TaskTags.push_back(data);
        }
    }
    QList<TaskTagData*> toDelete;
    for (TaskTagData *data : _TaskTags)
    {
        bool deleteTag = true;
        for (int id : idToKeep)
        {
            if (data->id() == id)
            {
                deleteTag = false;
                break;
            }
        }
        if (deleteTag)
            toDelete.push_back(data);
    }
    for (TaskTagData *data : toDelete)
        _TaskTags.removeAll(data);
    emit taskTagsChanged();
    if (_lastTagAdded != -1)
    {
        emit tagAdded(_lastTagAdded);
        _lastTagAdded = -1;
    }
}

void GanttModel::OnLoadTaskTagFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    emit error("Project task error", "Unable to retreive tag used in the project. Please try again later.");
}

void GanttModel::OnAddTagDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    qDebug() << "ID : " << obj["id"].toInt();
    _lastTagAdded = obj["id"].toInt();
    loadTaskTag();
}

void GanttModel::OnAddTagFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    emit error("Project task error", "Unable to add the tag. Please try again later.");
}

void GanttModel::OnRemoveTagDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    loadTaskTag();
}

void GanttModel::OnRemoveTagFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    emit error("Project task error", "Unable to remove the tag. Please try again later.");
}

void GanttModel::OnAddTaskDone(int id, QByteArray data)
{
    Q_UNUSED(id)
    loadTasks();
}

void GanttModel::OnAddTaskFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    emit error("Project task error", "Unable to add tasks.");
}
