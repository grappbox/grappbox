#include <QDebug>
#include "GanttModel.h"

GanttModel::GanttModel(QObject *parent) : QObject(parent)
{
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
        ADD_URL_FIELD(USER_TOKEN);
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
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_GANTT, API::GR_LIST_TASK_TAG);
    }
    END_REQUEST;
    _IsLoading = true;
    emit isLoadingChanged();
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

void GanttModel::addTag(QString name)
{
    qDebug() << "Add tag " << name;
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnAddTagDone");
        SET_ON_FAIL("OnAddTagFail");
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", PROJECT);
        ADD_FIELD("name", name);
        POST(API::DP_GANTT, API::PR_ADD_TAG_TASK);
    }
    END_REQUEST;
}

void GanttModel::removeTag(int id)
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnAddTagDone");
        SET_ON_FAIL("OnAddTagFail");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(id);
        DELETE_REQ(API::DP_GANTT, API::DR_TASK_TAG);
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
        TaskData *data = new TaskData();
        data->setId(task["id"].toInt());
        data->setTitle(task["title"].toString());
        data->setDescription(task["description"].toString());
        data->setColor(task["color"].toString());
        data->setIsMilestone(task["is_milestone"].toBool());
        data->setDueDate(JSON_TO_DATETIME(task["due_date"].toObject()["date"].toString()));
        data->setStartDate(JSON_TO_DATETIME(task["started_date"].toObject()["date"].toString()));
        data->setFinishDate(JSON_TO_DATETIME(task["created_date"].toObject()["date"].toString()));
        data->setCreateDate(JSON_TO_DATETIME(task["finished_date"].toObject()["date"].toString()));
        QList<UserData*> userData;
        QList<int> ressourceData;
        for (QJsonValueRef refUser : task["users_assigned"].toArray())
        {
            QJsonObject user = refUser.toObject();
            UserData *dataUser = new UserData();
            dataUser->setFirstName(user["firstname"].toString());
            dataUser->setLastName(user["lastname"].toString());
            dataUser->setId(user["id"].toInt());
            userData.push_back(dataUser);
            ressourceData.push_back(user["percent"].toInt());
        }
// TO FINISH !
        _Tasks.push_back(data);
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
                data = tmpTag;
                break;
            }
        }
        if (data == nullptr)
        {
            data = new TaskTagData(taskData["id"].toInt(), taskData["name"].toString());
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
    Q_UNUSED(data)
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
