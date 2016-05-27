#ifndef SDATAMANAGER_H
#define SDATAMANAGER_H

#include <QString>
#include <QImage>
#include <QJsonDocument>
#include <QJsonArray>
#include <QJsonObject>
#include <QVariant>
#include <QMap>
#include <QStringList>
#include <QFile>
#include <QJSEngine>
#include <QQmlEngine>
#include "UserData.h"
#include "ProjectData.h"
#include "SDebugLog.h"
#include "IDataConnector.h"

#define USER_TOKEN API::SDataManager::GetDataManager()->GetToken()
#define PROJECT API::SDataManager::GetDataManager()->GetCurrentProject()

#define BEGIN_REQUEST {QMap<QString, QVariant> __data; int __currentIndex = 0
#define SET_ON_DONE(value) const char *__onDone = value
#define SET_ON_FAIL(value) const char *__onFail = value
#define SET_CALL_OBJECT(object) QObject *__callObj = object
#define ADD_FIELD(name, value) __data[name] = value
#define ADD_FIELD_OBJECT(name, value, objectName) {QMap<QString, QVariant> __map = __data[objectName].toMap(); __map[name] = value; __data[objectName] = __map
#define ADD_FIELD_ARRAY(value, arrayName) {QList<QVariant> __list = __data[arrayName].toList(); __list.push_back(value); __data[arrayName] = __list;}
#define ADD_URL_FIELD(value) __data[QString("urlfield#") + QVariant(__currentIndex).toString()] = value; __currentIndex++
#define ADD_OBJECT(name) __data[name] = QMap<QString, QVariant>()
#define ADD_ARRAY(name) __data[name] = QList<QVariant>()
#define POST(part, request) API::SDataManager::GetCurrentDataConnector()->Request(API::RT_POST, part, request, __data, __callObj, __onDone, __onFail)
#define PUT(part, request) API::SDataManager::GetCurrentDataConnector()->Request(API::RT_PUT, part, request, __data, __callObj, __onDone, __onFail)
#define GET(part, request) API::SDataManager::GetCurrentDataConnector()->Request(API::RT_GET, part, request, __data, __callObj, __onDone, __onFail)
#define DELETE_REQ(part, request) API::SDataManager::GetCurrentDataConnector()->Request(API::RT_DELETE, part, request, __data, __callObj, __onDone, __onFail)
#define END_REQUEST }

#define GENERATE_JSON_DEBUG API::SDataManager::GenerateFileDebug(__data)
#define SHOW_JSON(param) API::SDataManager::GenerateFileDebug(param)

#define JSON_TO_DATETIME(date) QDateTime::fromString(date, "yyyy-MM-dd HH:mm:ss.zzzz")
#define JSON_TO_DATE(datep) JSON_TO_DATETIME((datep)).date()

namespace API
{

    class SDataManager : public QObject
    {
        Q_OBJECT

        Q_PROPERTY(UserData *user READ user WRITE setUser NOTIFY userChanged)
        Q_PROPERTY(ProjectData *project READ project WRITE setProject NOTIFY projectChanged)
        Q_PROPERTY(QVariantList projectList READ projectList WRITE setProjectList NOTIFY projectListChanged)
        Q_PROPERTY(bool hasProject READ hasProject NOTIFY hasProjectChanged)

    public:
        static IDataConnector      *GetCurrentDataConnector();
        static SDataManager        *GetDataManager();
        void                       RegisterUserConnected(int id, QString userName, QString userLastName, QString token, QImage *avatar);
        void                       LogoutUser();

        Q_INVOKABLE                updateCurrentProject()
        {
            BEGIN_REQUEST;
            {
                SET_CALL_OBJECT(this);
                SET_ON_DONE("UpdateProjectDone");
                SET_ON_FAIL("UpdateProjectFail");
                ADD_URL_FIELD(USER_TOKEN);
                ADD_URL_FIELD(PROJECT);
                GET(API::DP_PROJECT, API::GR_PROJECT);
            }
            END_REQUEST;
            BEGIN_REQUEST;
            {
                SET_CALL_OBJECT(this);
                SET_ON_DONE("UpdateProjectUserDone");
                SET_ON_FAIL("UpdateProjectUserFail");
                ADD_URL_FIELD(USER_TOKEN);
                ADD_URL_FIELD(PROJECT);
                GET(API::DP_PROJECT, API::GR_PROJECT_USERS);
            }
            END_REQUEST;
        }

        static void                Destroy();

		static QJsonObject ParseMapDebug(QMap<QString, QVariant> &data);
		static void GenerateFileDebug(QMap<QString, QVariant> &data);
		static void GenerateFileDebug(QByteArray arr);

        int                        GetUserId() const;
        QString                    GetUserName() const;
        QString                    GetUserLastName() const;
        QString                    GetToken() const;
        int                        GetCurrentProject() const;
        QImage                     *GetAvatar();

        void                       SetCurrentProjectId(int id);

        UserData *user() const
        {
            return m_user;
        }

        ProjectData *project() const
        {
            return m_project;
        }

        QVariantList projectList() const
        {
            return m_projectList;
        }

        bool hasProject() const
        {
            return _CurrentProject != -1;
        }

    public slots:
        void setUser(UserData *user)
        {
            m_user = user;
            emit userChanged(user);
        }

        void setProject(ProjectData *project)
        {
            m_project = project;
            _CurrentProject = project->id();
            emit hasProjectChanged(hasProject());
            emit projectChanged(project);
        }

        void setProjectList(QVariantList projectList)
        {
            if (m_projectList == projectList)
                return;

            m_projectList = projectList;
            emit projectListChanged(projectList);
        }

        void UpdateProjectDone(int id, QByteArray data)
        {
            QJsonDocument doc;
            doc = QJsonDocument::fromJson(data);
            QJsonObject obj = doc.object()["data"].toObject();
            m_project->setName(obj["name"].toString());
            m_project->setDescription(obj["description"].toString());
            m_project->setPhone(obj["phone"].toString());
            m_project->setCompany(obj["company"].toString());
            m_project->setMail(obj["contact_mail"].toString());
            m_project->setFacebook(obj["facebook"].toString());
            m_project->setTwitter(obj["twitter"].toString());
            m_project->setColor(QColor("#" + obj["color"].toString()));
            emit projectChanged(m_project);
        }

        void UpdateProjectFail(int id, QByteArray data)
        {
            Q_UNUSED(id)
            Q_UNUSED(data)
        }

        void UpdateProjectUserDone(int id, QByteArray data)
        {
            QJsonDocument doc;
            doc = QJsonDocument::fromJson(data);
            QJsonObject obj = doc.object()["data"].toObject();
            QVariantList list;
            for (QJsonValueRef ref : obj["array"].toArray())
            {
                UserData *data = new UserData();
                data->setId(ref.toObject()["id"].toInt());
                data->setFirstName(ref.toObject()["firstname"].toString());
                data->setLastName(ref.toObject()["lastname"].toString());
                list.push_back(qVariantFromValue(data));
            }
            m_project->setUsers(list);
            emit projectChanged(m_project);
        }

        void UpdateProjectUserFail(int id, QByteArray data)
        {
            Q_UNUSED(id)
            Q_UNUSED(data)
        }

    signals:
        void userChanged(UserData *user);

        void projectChanged(ProjectData *project);

        void projectListChanged(QVariantList projectList);

        void hasProjectChanged(bool hasProject);

    private:
        SDataManager();
        ~SDataManager();

        IDataConnector      *_OfflineDataConnector;
        IDataConnector      *_OnlineDataConnector;

        int                 _UserId;
        QString             _UserName;
        QString             _UserLastName;
        QString             _Token;
        QImage              *_Avatar;
        int                 _CurrentProject;

        UserData *m_user;
        ProjectData *m_project;
        QVariantList m_projectList;
    };
}

#endif // SDATAMANAGER_H
