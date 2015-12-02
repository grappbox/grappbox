#ifndef DATACONNECTORONLINE_H
#define DATACONNECTORONLINE_H

#include <QtNetwork/QNetworkAccessManager>
#include <QtNetwork/QNetworkReply>
#include <QJSONObject>
#include <QJsonDocument>
#include <QMap>
#include <QTimeZone>
#include <QBuffer>

#include "IDataConnector.h"

#define URL_API QString("http://api.grappbox.com/app_dev.php/V0.7/")

namespace API
{
    struct DataConnectorCallback
    {
        QObject *_Request;
        const char *_SlotSuccess;
        const char *_SlotFailure;
    };

    class DataConnectorOnline : public QObject, public IDataConnector
    {
        Q_OBJECT
    public:
        DataConnectorOnline();

        virtual int Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);
        virtual int Get(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);
        virtual int Delete(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);
        virtual int Put(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);

    signals:
        void responseAPISuccess(int, QByteArray);
        void responseAPIFailure(int, QByteArray);

    public slots:
        void OnResponseAPI();

    private:
        QMap<QNetworkReply*, int> _Request;
        QMap<QNetworkReply*, DataConnectorCallback> _CallBack;
        QNetworkAccessManager *_Manager;

        //Put
    private:
        QNetworkReply *PutUserSettings(QVector<QString> &data);
        QNetworkReply *PutProjectSettings(QVector<QString> &data);

        // Post
    private:
        QNetworkReply *Login(QVector<QString> &data);
        QNetworkReply *AddRole(QVector<QString> &data);
        QNetworkReply *AttachRole(QVector<QString> &data);
        QNetworkReply *ProjectInvite(QVector<QString> &data);

        // Delete
    private:
        QNetworkReply *DeleteProjectRole(QVector<QString> &data);
        QNetworkReply *DetachRole(QVector<QString> &data);
        QNetworkReply *DeleteProjectUser(QVector<QString> &data);

        // Get
    private:
        QNetworkReply *Logout(QVector<QString> &data);
        QNetworkReply *GetAction(QString urlIn, QVector<QString> &data);
    };

}

#endif // DATACONNECTORONLINE_H
