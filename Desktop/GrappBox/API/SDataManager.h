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
#include <QMessageBox>
#include "IDataConnector.h"

#define BEGIN_REQUEST {QMap<QString, QVariant> __data; int __currentIndex = 0
#define SET_ON_DONE(value) const char *__onDone = value
#define SET_ON_FAIL(value) const char *__onFail = value
#define SET_CALL_OBJECT(object) QObject *__callObj = object
#define ADD_FIELD(name, value) __data[name] = value
#define ADD_FIELD_OBJECT(name, value, objectName) {QMap<QString, QVariant> __map = __data[objectName].toMap(); __map[name] = value; __data[objectName] = __map
#define ADD_FIELD_ARRAY(value, arrayName) QList<QVariant> __list = __data[objectName].toList(); __map.push_back(value); __data[objectName] = __list
#define ADD_URL_FIELD(value) __data[QString("urlfield#") + QVariant(__currentIndex).toString()] = value; __currentIndex++
#define ADD_OBJECT(name) __data[name] = QMap<QString, QVariant>()
#define ADD_ARRAY(name) __data[name] = QList<QVariant>()
#define POST(part, request) API::SDataManager::GetCurrentDataConnector()->Request(API::RT_POST, part, request, __data, __callObj, __onDone, __onFail)
#define PUT(part, request) API::SDataManager::GetCurrentDataConnector()->Request(API::RT_PUT, part, request, __data, __callObj, __onDone, __onFail)
#define GET(part, request) API::SDataManager::GetCurrentDataConnector()->Request(API::RT_GET, part, request, __data, __callObj, __onDone, __onFail)
#define DELETE(part, request) API::SDataManager::GetCurrentDataConnector()->Request(API::RT_DELETE, part, request, __data, __callObj, __onDone, __onFail)
#define END_REQUEST }

#define GENERATE_JSON_DEBUG API::SDataManager::GenerateFileDebug(__data)
#define SHOW_JSON(param) API::SDataManager::GenerateFileDebug(param)

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
