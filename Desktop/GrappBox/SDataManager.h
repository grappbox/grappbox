#ifndef SDATAMANAGER_H
#define SDATAMANAGER_H

#include "IDataConnector.h"

namespace API
{

    class SDataManager
    {
    public:
        static IDataConnector      *GetCurrentDataConnector();
        static void                Destroy();

    private:
        SDataManager();
        ~SDataManager();

        IDataConnector      *_OfflineDataConnector;
        IDataConnector      *_OnlineDataConnector;
    };

    static SDataManager *__INSTANCE__SDataManager = NULL;

}

#endif // SDATAMANAGER_H
