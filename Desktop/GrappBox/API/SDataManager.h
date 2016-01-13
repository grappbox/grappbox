#ifndef SDATAMANAGER_H
#define SDATAMANAGER_H

#include <QString>
#include <QImage>
#include "IDataConnector.h"

namespace API
{

    class SDataManager
    {
    public:
        static IDataConnector      *GetCurrentDataConnector();
        static SDataManager        *GetDataManager();
        void                       RegisterUserConnected(int id, QString userName, QString userLastName, QString token, QImage *avatar);
        void                       LogoutUser();
        static void                Destroy();

        int                        GetUserId() const;
        QString                    GetUserName() const;
        QString                    GetUserLastName() const;
        QString                    GetToken() const;
        int                        GetCurrentProject() const;
        QImage                     *GetAvatar();

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
        QImage              *_Avatar;
        int                 _CurrentProject;

    };

}

#endif // SDATAMANAGER_H
