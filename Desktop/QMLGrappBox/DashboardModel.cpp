#include "DashboardModel.h"
#include "Manager/SInfoManager.h"

DashboardModel::DashboardModel() : QObject(nullptr)
{
    m_isLoading[0] = false;
    m_isLoading[1] = false;
    m_isLoading[2] = false;
}

void DashboardModel::loadProjectList()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnLoadProjectListDone");
        SET_ON_FAIL("OnLoadProjectListFail");
        ADD_URL_FIELD(USER_TOKEN);
        GET(API::DP_PROJECT, API::GR_LIST_PROJECT);
    }
    END_REQUEST;
    m_isLoading[0] = true;
    emit isLoadingChanged(isLoading());
}

void DashboardModel::loadUserProjectList()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnLoadUserListDone");
        SET_ON_FAIL("OnLoadUserListFail");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_PROJECT, API::GR_TEAM_OCCUPATION);
    }
    END_REQUEST;
    m_isLoading[1] = true;
    emit isLoadingChanged(isLoading());
}

void DashboardModel::loadNewEventList()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnLoadEventListDone");
        SET_ON_FAIL("OnLoadEventListFail");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        GET(API::DP_PROJECT, API::GR_NEXT_MEETING);
    }
    END_REQUEST;
    m_isLoading[2] = true;
    emit isLoadingChanged(isLoading());
}

void DashboardModel::selectProject(ProjectData *project)
{
    API::SDataManager::GetDataManager()->setProject(project);
    m_userProjectList.clear();
    m_newEventList.clear();
    loadUserProjectList();
    loadNewEventList();
}

void DashboardModel::addANewProject(ProjectData *project, QString securedPassword)
{
    BEGIN_REQUEST_ADV(this, "OnCreateProjectDone", "OnCreateProjectFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("name", project->name());
        ADD_FIELD("description", project->description());
        ADD_FIELD("phone", project->phone());
        ADD_FIELD("company", project->company());
        ADD_FIELD("email", project->mail());
        ADD_FIELD("facebook", project->facebook());
        ADD_FIELD("twitter", project->twitter());
        ADD_FIELD("password", securedPassword);
        POST(API::DP_PROJECT, API::PR_CREATE_PROJECT);
    }
    END_REQUEST;
}

void DashboardModel::OnLoadProjectListDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.2.1"
            && info["return_code"].toString() != "1.2.3")
    {
        OnLoadProjectListFail(id, data);
        return;
    }
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject project = ref.toObject();
        ProjectData *data;
        bool add = true;
        for (ProjectData *tmp : m_projectList)
        {
            if (tmp->id() == project["project_id"].toInt())
            {
                data = tmp;
                add = false;
                break;
            }
        }
        if (add)
            data = new ProjectData();
        data->setId(project["project_id"].toInt());
        data->setName(project["project_name"].toString());
        data->setDescription(project["project_description"].toString());
        data->setPhone(project["project_phone"].toString());
        data->setCompany(project["project_company"].toString());
        // Logo Here
        data->setMail(project["contact_mail"].toString());
        data->setFacebook(project["facebook"].toString());
        data->setTwitter(project["twitter"].toString());
        data->setNumTaskFinished(project["number_finished_tasks"].toInt());
        data->setNumTaskOnGoing(project["number_ongoing_tasks"].toInt());
        data->setNumTaskTotal(project["number_tasks"].toInt());
        data->setNumBugTotal(project["number_bugs"].toInt());
        data->setNumMessageTimeline(project["number_messages"].toInt());
        if (add)
            m_projectList.push_back(data);
    }
    emit projectListChanged(projectList());
}

void DashboardModel::OnLoadProjectListFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    emit error("Dashboard", "Unable to retrieve project. Please try again later.");
}

void DashboardModel::OnLoadUserListDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.2.1"
            && info["return_code"].toString() != "1.2.3")
    {
        OnLoadProjectListFail(id, data);
        return;
    }
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject user = ref.toObject()["user"].toObject();
        UserData *data;
        bool add = true;
        for (UserData *tmp : m_userProjectList)
        {
            if (tmp->id() == user["id"].toInt())
            {
                data = tmp;
                add = false;
                break;
            }
        }
        if (add)
            data = new UserData();
        data->setId(user["id"].toInt());
        data->setFirstName(user["firstname"].toString());
        data->setLastName(user["lastname"].toString());
        data->setOccupation(ref.toObject()["occupation"].toString() == "free" ? 0 : 1);
        if (add)
            m_userProjectList.push_back(data);
    }
    emit userProjectListChanged(userProjectList());
}

void DashboardModel::OnLoadUserListFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    emit error("Dashboard", "Unable to retrieve the list of user in your project. Please try again later.");
}

void DashboardModel::OnLoadEventListDone(int id, QByteArray data)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() != "1.2.1"
            && info["return_code"].toString() != "1.2.3")
    {
        OnLoadProjectListFail(id, data);
        return;
    }
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject event = ref.toObject();
        EventData *data;
        bool add = true;
        /*for (EventData *tmp : m_newEventList)
        {
            if (tmp->id() == event["id"].toInt())
            {
                data = tmp;
                add = false;
                break;
            }
        }*/
        if (add)
            data = new EventData();
        //data->setId(user["id"].toInt());
        data->setType(event["type"].toString());
        data->setTitle(event["title"].toString());
        data->setDescription(event["description"].toString());
        data->setStartDate(JSON_TO_DATETIME(event["begin_date"].toObject()["date"].toString()));
        data->setEndDate(JSON_TO_DATETIME(event["end_date"].toObject()["date"].toString()));
        if (add)
            m_newEventList.push_back(data);
    }
    emit newEventListChanged(newEventList());
}

void DashboardModel::OnLoadEventListFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    emit error("Dashboard", "Unable to retrieve the list of next events in your project. Please try again later.");
}

void DashboardModel::OnCreateProjectDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    loadProjectList();
    SInfoManager::GetManager()->info("Project created!");
}

void DashboardModel::OnCreateProjectFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->error("Project creation", "Unable to create the project.");
}
