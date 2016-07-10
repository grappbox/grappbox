#include <QDebug>

#include "SDataManager.h"
#include "DataConnectorOnline.h"

using namespace API;

static SDataManager *__INSTANCE__SDataManager = nullptr;

SDataManager::SDataManager()
{
    _OfflineDataConnector = nullptr;
    _OnlineDataConnector = new DataConnectorOnline;
    _UserId = -1;
    _CurrentProject = -1;
    m_project = new ProjectData();
    m_user = new UserData();
}

SDataManager::~SDataManager()
{
    if (_OfflineDataConnector != nullptr)
        delete _OfflineDataConnector;
    delete _OnlineDataConnector;
}

IDataConnector       *SDataManager::GetCurrentDataConnector()
{
    if (__INSTANCE__SDataManager == nullptr)
        __INSTANCE__SDataManager = new SDataManager();
    if (1) // Temporary ! Condtion is : if user is online ?
        return __INSTANCE__SDataManager->_OnlineDataConnector;
    return __INSTANCE__SDataManager->_OfflineDataConnector;
}

SDataManager      *SDataManager::GetDataManager()
{
    if (__INSTANCE__SDataManager == nullptr)
        __INSTANCE__SDataManager = new SDataManager();
    return (__INSTANCE__SDataManager);
}

void                       SDataManager::RegisterUserConnected(int id, QString userName, QString userLastName, QString token, QImage *avatar)
{
	Q_UNUSED(avatar)
    _UserId = id;
    _UserName = userName;
    _UserLastName = userLastName;
    m_user->setId(id);
    m_user->setFirstName(userName);
    m_user->setLastName(userLastName);
    _Token = token;
}

void                       SDataManager::LogoutUser()
{
    _UserId = -1;
}

int                        SDataManager::GetUserId() const
{
    return _UserId;
}

QString                    SDataManager::GetUserName() const
{
    if (_UserId == -1)
        return "";
    return _UserName;
}

QString                    SDataManager::GetUserLastName() const
{
    if (_UserId == -1)
        return "";
    return _UserLastName;
}

QString                    SDataManager::GetToken() const
{
    if (_UserId == -1)
        return "";
    return _Token;
}

QImage                     *SDataManager::GetAvatar()
{
    return _Avatar;
}

int                        SDataManager::GetCurrentProject() const
{
    return _CurrentProject;
}

void                       SDataManager::SetCurrentProjectId(int id)
{
    _CurrentProject = id;
    if (_CurrentProject < 0)
        _CurrentProject = -1;
    else
    {
        BEGIN_REQUEST_ADV(this, "UpdateProjectUserDone", "UpdateProjectUserFail");
        {
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(id);
            GET(API::DP_PROJECT, API::GR_TEAM_OCCUPATION);
        }
        END_REQUEST;
    }
    emit hasProjectChanged(hasProject());
}

QJsonObject SDataManager::ParseMapDebug(QMap<QString, QVariant> &data)
{
	QJsonObject ret;
	for (QMap<QString, QVariant>::iterator it = data.begin(); it != data.end(); ++it)
	{
        qDebug() << "Parse value key : " << it.key();
		if (it.value().canConvert<QString>())
			ret[it.key()] = it.value().toString();
        else if (it.value().canConvert<QList<QVariant> >())
		{
            qDebug() << "Parse array with name : " << it.key();
			QJsonArray arr;
            QList<QVariant> strList = it.value().toList();
            for (QVariant str : strList)
                arr.append(str.toString());
			ret[it.key()] = arr;
		}
		else
        {
            QMap<QString, QVariant> newData = it.value().toMap();
            ret[it.key()] = ParseMapDebug(newData);
        }
    }
	return ret;
}

void SDataManager::GenerateFileDebug(QMap<QString, QVariant> &data)
{
	QJsonObject ret = SDataManager::ParseMapDebug(data);
	QJsonDocument doc(ret);
	QString json = doc.toJson(QJsonDocument::Indented);
    qDebug() << "==========JSON DEBUG=========";
    qDebug() << json;
	//QMessageBox::about(nullptr, "Json debug", json);
}

void API::SDataManager::GenerateFileDebug(QByteArray arr)
{
	QJsonDocument doc = QJsonDocument::fromJson(arr);
	QString json = doc.toJson(QJsonDocument::Indented);
    qDebug() << "==========JSON DEBUG=========";
    qDebug() << json;
	//QMessageBox::about(nullptr, "Json debug", json);
}
