#ifndef SDATAMANAGER_H
#define SDATAMANAGER_H

#include <QString>
#include "IDataConnector.h"

namespace API
{

    class SDataManager
    {
    public:
        static IDataConnector      *GetCurrentDataConnector();
        static SDataManager        *GetDataManager();
        void                       RegisterUserConnected(int id, QString userName, QString userLastName, QString token);
        void                       LogoutUser();
        static void                Destroy();

        int                        GetUserId();
        QString                    GetUserName();
        QString                    GetUserLastName();
        QString                    GetToken();

    private:
        SDataManager();
        ~SDataManager();

        IDataConnector      *_OfflineDataConnector;
        IDataConnector      *_OnlineDataConnector;

        int                 _UserId;
        QString             _UserName;
        QString             _UserLastName;
        QString             _Token;

    };

}

#endif // SDATAMANAGER_H
