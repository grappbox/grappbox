#include "StatisticsModel.h"

StatisticsModel::StatisticsModel()
{
    m_projectInfo["property"] = QVariantMap();
    m_bugTrackerInfo["property"] = QVariantMap();
    m_taskInfo["property"] = QVariantMap();
    m_userInfo["property"] = QVariantMap();
}

void StatisticsModel::updateStatisticsInfo()
{
    BEGIN_REQUEST_ADV(this, "OnUpdateDone", "OnUpdateFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_PROJECT, API::GR_STAT);
    }
    END_REQUEST;
}

void StatisticsModel::UpdateProject(QJsonObject obj)
{
    m_projectInfo["cloud"] = QVariant((float)obj["storageSize"].toObject()["occupied"].toInt() * 100.0f
            / (float)obj["storageSize"].toObject()["total"].toInt());
    m_projectInfo["clientTimeline"] = QVariant(obj["timelinesMessageNumber"].toObject()["customer"].toString()).toInt();
    m_projectInfo["teamTimeline"] = QVariant(obj["timelinesMessageNumber"].toObject()["team"].toString()).toInt();
    QDateTime today = QDateTime::currentDateTimeUtc();
    QDateTime start = JSON_TO_DATETIME_CUSTOM(obj["projectTimeLimits"].toObject()["projectStart"].toObject()["date"].toString(), "yyyy-MM-dd HH:mm:ss.zzzz");
    QDateTime end = JSON_TO_DATETIME_CUSTOM(obj["projectTimeLimits"].toObject()["projectEnd"].toObject()["date"].toString(), "yyyy-MM-dd HH:mm:ss.zzzz");
    qDebug() << start.daysTo(today) << " : " << today.daysTo(end);
    qDebug() << "TODAY : " << today;
    m_projectInfo["dayPassed"] = start.daysTo(today);
    m_projectInfo["dayRemaining"] = today.daysTo(end);
    m_projectInfo["clientActual"] = obj["customerAccessNumber"].toObject()["actual"].toInt();
    m_projectInfo["clientMax"] = obj["customerAccessNumber"].toObject()["maximum"].toInt();
    QMap<QString, QVariantList> userLate;
    QMap<QString, QVariantList> roleLate;
    for (QJsonValueRef itemRef : obj["lateTask"].toArray())
    {
        QJsonObject item = itemRef.toObject();

        QVariantList tmpList;
        tmpList.push_back((float)(item["lateTasks"].toInt()) + 0.1f);
        tmpList.push_back((float)(item["ontimeTasks"].toInt()) + 0.1f);

        userLate[item["user"].toObject()["firstname"].toString()
                + " " + item["user"].toObject()["lastname"].toString()] = tmpList;

        if (roleLate.contains(item["role"].toString()))
        {
            QVariantList l = roleLate[item["role"].toString()];
            l[0] = l[0].toFloat() + (float)item["lateTasks"].toInt();
            l[1] = l[1].toFloat() + (float)item["ontimeTasks"].toInt();
            roleLate[item["role"].toString()] = l;
        }
        else
        {
            roleLate[item["role"].toString()] = tmpList;
        }
    }
    QVariantList userList;
    QVariantList roleList;
    for (QMap<QString, QVariantList>::iterator it = userLate.begin(); it != userLate.end(); ++it)
    {
        QVariantMap tmpMap;
        tmpMap["label"] = it.key();
        tmpMap["value"] = it.value();
        userList.push_back(tmpMap);
    }
    for (QMap<QString, QVariantList>::iterator it = roleLate.begin(); it != roleLate.end(); ++it)
    {
        QVariantMap tmpMap;
        tmpMap["label"] = it.key();
        tmpMap["value"] = it.value();
        roleList.push_back(tmpMap);
    }
    m_projectInfo["userLate"] = userList;
    m_projectInfo["roleLate"] = roleList;
    projectInfoChanged(m_projectInfo);
}

void StatisticsModel::UpdateTask(QJsonObject obj)
{
    m_taskInfo["taskDone"] = QVariant(obj["taskStatus"].toObject()["done"].toString()).toInt();
    m_taskInfo["taskDoing"] = QVariant(obj["taskStatus"].toObject()["doing"].toString()).toInt();
    m_taskInfo["taskToDo"] = QVariant(obj["taskStatus"].toObject()["toDo"].toString()).toInt();
    m_taskInfo["taskLate"] = QVariant(obj["taskStatus"].toObject()["late"].toString()).toInt();
    m_taskInfo["taskTotal"] = QVariant(obj["totalTasks"].toString()).toInt();
    QMap<QString, float> tasksRep;
    int totalAssigned = 0;
    if (m_taskInfo["taskTotal"].toInt() > 0)
    {
        for (QJsonValueRef itemRef : obj["tasksRepartition"].toArray())
        {
            QJsonObject item = itemRef.toObject();

            if (item["value"].toInt() == 0)
                continue;
            totalAssigned = item["value"].toInt();
            tasksRep[item["user"].toObject()["firstname"].toString()
                    + " " + item["user"].toObject()["lastname"].toString()] = item["value"].toInt() * 100 / m_taskInfo["taskTotal"].toInt();
        }
        if (totalAssigned != m_taskInfo["taskTotal"].toInt())
            tasksRep["Unassigned"] = (m_taskInfo["taskTotal"].toInt() - totalAssigned) * 100 / m_taskInfo["taskTotal"].toInt();
        QVariantList userTask;
        for (QMap<QString, float>::iterator it = tasksRep.begin(); it != tasksRep.end(); ++it)
        {
            QVariantMap tmpMap;
            tmpMap["label"] = it.key();
            tmpMap["value"] = it.value();
            userTask.push_back(tmpMap);
        }
        m_taskInfo["taskRepartition"] = userTask;
    }
    qDebug() << "Test";
    taskInfoChanged(m_taskInfo);
}

void StatisticsModel::UpdateBugTracker(QJsonObject obj)
{
    m_bugTrackerInfo["clientBug"] = QVariant(obj["clientBugTracker"].toString()).toInt();
    m_bugTrackerInfo["openBug"] = QVariant(obj["openCloseBug"].toObject()["open"].toString()).toInt();
    m_bugTrackerInfo["closeBug"] = QVariant(obj["openCloseBug"].toObject()["closed"].toString()).toInt();
    m_bugTrackerInfo["totalBug"] = m_bugTrackerInfo["openBug"].toInt() + m_bugTrackerInfo["closeBug"].toInt();
    m_bugTrackerInfo["assignedBug"] = obj["bugAssignationTracker"].toObject()["assigned"].toInt();
    m_bugTrackerInfo["unassignedBug"] = obj["bugAssignationTracker"].toObject()["unassigned"].toInt();

    QMap<QString, QVariantList> bugEvolution;
    for (QJsonValueRef itemRef : obj["lateTask"].toArray())
    {
        QJsonObject item = itemRef.toObject();

        QVariantList tmpList;
        tmpList.push_back(item["createdBugs"].toInt());
        tmpList.push_back(item["closedBugs"].toInt());

        bugEvolution[JSON_TO_DATETIME(item["date"].toObject()["date"].toString()).toString("MMMM yyyy")] = tmpList;
    }
    QVariantList bugList;
    for (QMap<QString, QVariantList>::iterator it = bugEvolution.begin(); it != bugEvolution.end(); ++it)
    {
        QVariantMap tmpMap;
        tmpMap["label"] = it.key();
        tmpMap["value"] = it.value();
        bugList.push_back(tmpMap);
    }
    m_bugTrackerInfo["bugEvolution"] = bugList;

    QMap<QString, float> bugRepTag;
    for (QJsonValueRef itemRef : obj["bugsTagsRepartition"].toArray())
    {
        QJsonObject item = itemRef.toObject();

        if (item["value"].toInt() == 0)
            continue;
        bugRepTag[item["name"].toString()] = item["value"].toInt();
    }
    QVariantList bugRep;
    for (QMap<QString, float>::iterator it = bugRepTag.begin(); it != bugRepTag.end(); ++it)
    {
        QVariantMap tmpMap;
        tmpMap["label"] = it.key();
        tmpMap["value"] = it.value();
        bugRep.push_back(tmpMap);
    }
    m_bugTrackerInfo["bugRepartitionTag"] = bugRep;

    QMap<QString, float> bugRepUser;
    for (QJsonValueRef itemRef : obj["bugsUsersRepartition"].toArray())
    {
        QJsonObject item = itemRef.toObject();

        if (item["value"].toInt() == 0)
            continue;
        bugRepUser[item["user"].toObject()["firstname"].toString()
                + " " + item["user"].toObject()["lastname"].toString()] = item["value"].toInt();
    }
    QVariantList bugRepU;
    for (QMap<QString, float>::iterator it = bugRepUser.begin(); it != bugRepUser.end(); ++it)
    {
        QVariantMap tmpMap;
        tmpMap["label"] = it.key();
        tmpMap["value"] = it.value();
        bugRepU.push_back(tmpMap);
    }
    m_bugTrackerInfo["bugRepartitionUser"] = bugRepU;

    bugTrackerInfoChanged(m_bugTrackerInfo);
}

void StatisticsModel::UpdateUser(QJsonObject obj)
{
    m_userInfo["workingCharge"] = obj["userWorkingCharge"].toVariant();
    QMap<QString, QVariantList> taskAdv;
    for (QJsonValueRef itemRef : obj["userTasksAdvancement"].toArray())
    {
        QJsonObject item = itemRef.toObject();

        QVariantList l;
        l.push_back((float)(item["tasksDone"].toInt()) + 0.0f);
        l.push_back((float)(item["tasksDoing"].toInt()) + 0.0f);
        l.push_back((float)(item["tasksToDo"].toInt()) + 0.0f);
        l.push_back((float)(item["tasksLate"].toInt()) + 0.0f);

        taskAdv[item["user"].toObject()["firstname"].toString()
                + " " + item["user"].toObject()["lastname"].toString()] = l;
    }
    QVariantList taskAdvUser;
    for (QMap<QString, QVariantList>::iterator it = taskAdv.begin(); it != taskAdv.end(); ++it)
    {
        QVariantMap tmpMap;
        tmpMap["label"] = it.key();
        tmpMap["value"] = it.value();
        taskAdvUser.push_back(tmpMap);
    }
    m_userInfo["taskState"] = taskAdvUser;
    userInfoChanged(m_userInfo);
}

void StatisticsModel::OnUpdateDone(int id, QByteArray array)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    UpdateProject(obj);
    UpdateTask(obj);
    UpdateBugTracker(obj);
    UpdateUser(obj);
    emit loaded();
}

void StatisticsModel::OnUpdateFail(int id, QByteArray array)
{

}
