#ifndef DASHBOARDMODEL_H
#define DASHBOARDMODEL_H

#include <QObject>
#include "ProjectData.h"
#include "UserData.h"
#include "EventData.h"
#include "API/SDataManager.h"

class DashboardModel : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QVariantList projectList READ projectList WRITE setProjectList NOTIFY projectListChanged)
    Q_PROPERTY(QVariantList userProjectList READ userProjectList WRITE setUserProjectList NOTIFY userProjectListChanged)
    Q_PROPERTY(QVariantList newEventList READ newEventList WRITE setNewEventList NOTIFY newEventListChanged)
    Q_PROPERTY(bool isLoading READ isLoading NOTIFY isLoadingChanged)

public:
    DashboardModel();
    ~DashboardModel()
    {
        API::SDataManager::GetCurrentDataConnector()->unregisterObjectRequest(this);
    }

    Q_INVOKABLE void loadProjectList();
    Q_INVOKABLE void loadUserProjectList();
    Q_INVOKABLE void loadNewEventList();
    Q_INVOKABLE void selectProject(ProjectData *project);
    Q_INVOKABLE void addANewProject(ProjectData *project, QString securedPassword);
    // Here put the statistics load

    QVariantList projectList() const
    {
        QVariantList list;
        for (ProjectData *data : m_projectList)
            list.append(qVariantFromValue(data));
        return list;
    }

    QVariantList userProjectList() const
    {
        QVariantList list;
        for (UserData *data : m_userProjectList)
            list.append(qVariantFromValue(data));
        return list;
    }

    QVariantList newEventList() const
    {
        QVariantList list;
        for (EventData *data : m_newEventList)
            list.append(qVariantFromValue(data));
        return list;
    }

    bool isLoading() const
    {
        return m_isLoading[0] || m_isLoading[1] || m_isLoading[2];
    }

    void setProjectList(QVariantList projectListP)
    {
        m_projectList.clear();
        for (QVariant var : projectListP)
        {
            ProjectData *obj = qobject_cast<ProjectData*>(var.value<ProjectData*>());
            if (obj != nullptr)
                m_projectList.push_back(obj);
        }
        QVariantList list = projectList();
        emit projectListChanged(list);
    }

    void setUserProjectList(QVariantList userProjectListP)
    {
        m_userProjectList.clear();
        for (QVariant var : userProjectListP)
        {
            UserData *obj = qobject_cast<UserData*>(var.value<UserData*>());
            if (obj != nullptr)
                m_userProjectList.push_back(obj);
        }
        QVariantList list = userProjectList();
        emit userProjectListChanged(list);
    }

    void setNewEventList(QVariantList newEventListP)
    {
        m_newEventList.clear();
        for (QVariant var : newEventListP)
        {
            EventData *obj = qobject_cast<EventData*>(var.value<EventData*>());
            if (obj != nullptr)
                m_newEventList.push_back(obj);
        }
        QVariantList list = newEventList();
        emit newEventListChanged(list);
    }

signals:

    void projectListChanged(QVariantList projectList);

    void userProjectListChanged(QVariantList userProjectList);

    void newEventListChanged(QVariantList newEventList);

    void isLoadingChanged(bool newIsLoading);

    void error(QString errorTitle, QString errorMessage);

    void isLoadingProjectChanged(bool isLoadingProject);

public slots:

    void OnLoadProjectListDone(int id, QByteArray data);
    void OnLoadProjectListFail(int id, QByteArray data);
    void OnLoadUserListDone(int id, QByteArray data);
    void OnLoadUserListFail(int id, QByteArray data);
    void OnLoadEventListDone(int id, QByteArray data);
    void OnLoadEventListFail(int id, QByteArray data);
    void OnCreateProjectDone(int id, QByteArray data);
    void OnCreateProjectFail(int id, QByteArray data);

private:
    QList<ProjectData*> m_projectList;

    QList<UserData*> m_userProjectList;

    QList<EventData*> m_newEventList;

    bool m_isLoading[3];
};

#endif // DASHBOARDMODEL_H
