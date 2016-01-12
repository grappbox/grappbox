#include <QDebug>

#include "SDataManager.h"
#include "DataConnectorOnline.h"

using namespace API;

static SDataManager *__INSTANCE__SDataManager = nullptr;

SDataManager::SDataManager()
{
    _OnlineDataConnector = new DataConnectorOnline;
    _UserId = -1;
    _CurrentProject = -1;
}

SDataManager::~SDataManager()
{
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

void                       SDataManager::RegisterUserConnected(int id, QString userName, QString userLastName, QString token)
{
    _UserId = id;
    _UserName = userName;
    _UserLastName = userLastName;
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

int                        SDataManager::GetCurrentProject() const
{
    return _CurrentProject;
}

void                       SDataManager::SetCurrentProjectId(int id)
{
    _CurrentProject = id;
    if (_CurrentProject < 0)
        _CurrentProject = -1;
}
