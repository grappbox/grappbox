#ifndef UTILS
#define UTILS

#define USER_TOKEN API::SDataManager::GetDataManager()->GetToken()
#define USER_ID API::SDataManager::GetDataManager()->GetUserId()
#define CURRENT_PROJECT API::SDataManager::GetDataManager()->GetCurrentProject()
#define TO_STRING(var)  QVariant(var).toString()
#define DATA_CONNECTOR API::SDataManager::GetCurrentDataConnector()
#define FORMAT_DATE "yyyy-MM-dd HH:mm:ss.zzzz"

#endif // UTILS

