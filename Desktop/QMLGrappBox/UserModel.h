#ifndef USERMODEL_H
#define USERMODEL_H

#include <QObject>
#include "API/SDataManager.h"
#include "UserData.h"

class UserModel : public QObject
{
    Q_OBJECT
    Q_PROPERTY(bool isLoading READ isLoading NOTIFY isLoadingChanged)

public:
    explicit UserModel(QObject *parent = 0);
    Q_INVOKABLE void getUserModel();
    Q_INVOKABLE void setUserModel(UserData *user, QString oldPassword = "", QString newPassword = "");

    bool isLoading();

signals:
    void isLoadingChanged(bool isLoading);
    void userChangedSuccess();
    void error(QString title, QString message);

public slots:
    void onGetUserDone(int id, QByteArray data);
    void onGetUserFail(int id, QByteArray data);
    void onSetUserDone(int id, QByteArray data);
    void onSetUserFail(int id, QByteArray data);

private:
    bool m_isLoading;
};

#endif // USERMODEL_H
