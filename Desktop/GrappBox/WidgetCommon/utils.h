#ifndef UTILS
#define UTILS

#define USER_TOKEN API::SDataManager::GetDataManager()->GetToken()
#define USER_ID API::SDataManager::GetDataManager()->GetUserId()
#define TO_STRING(var)  QVariant(var).toString()
#define DATA_CONNECTOR API::SDataManager::GetCurrentDataConnector()

#endif // UTILS

