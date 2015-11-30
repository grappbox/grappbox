#ifndef DATACONNECTORONLINE_H
#define DATACONNECTORONLINE_H

#include <QtNetwork/QNetworkAccessManager>
#include <QtNetwork/QNetworkReply>
#include <QJSONObject>
#include <QJsonDocument>
#include <QMap>

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

    signals:
        void responseAPISuccess(int, QByteArray);
        void responseAPIFailure(int, QByteArray);

    public slots:
        void OnResponseAPI();

    private:
        QMap<QNetworkReply*, int> _Request;
        QMap<QNetworkReply*, DataConnectorCallback> _CallBack;
        QNetworkAccessManager *_Manager;

        // Post
    private:
        QNetworkReply *Login(QVector<QString> &data);

        // Get
    private:
        QNetworkReply *Logout(QVector<QString> &data);
        QNetworkReply *GetAction(QString urlIn, QVector<QString> &data);
    };

}

#endif // DATACONNECTORONLINE_H
