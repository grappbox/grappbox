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

        int                        GetUserId() const;
        QString                    GetUserName() const;
        QString                    GetUserLastName() const;
        QString                    GetToken() const;
        int                        GetCurrentProject() const;

        void                       SetCurrentProjectId(int id);

    private:
        SDataManager();
        ~SDataManager();

        IDataConnector      *_OfflineDataConnector;
        IDataConnector      *_OnlineDataConnector;

        int                 _UserId;
        QString             _UserName;
        QString             _UserLastName;
        QString             _Token;
        int                 _CurrentProject;

    };

}

#endif // SDATAMANAGER_H
