#include "SDataManager.h"

using namespace API;

SDataManager::SDataManager()
{
    // Temporary ! New offlinedataconnector and onlinedataconnector here !
}

SDataManager::~SDataManager()
{
    delete _OfflineDataConnector;
    delete _OnlineDataConnector;
}

IDataConnector       *SDataManager::GetCurrentDataConnector()
{
    if (__INSTANCE__SDataManager == NULL)
        __INSTANCE__SDataManager = new SDataManager();
    if (1) // Temporary ! Condtion is : if user is online ?
        return __INSTANCE__SDataManager->_OnlineDataConnector;
    return __INSTANCE__SDataManager->_OfflineDataConnector;
}
