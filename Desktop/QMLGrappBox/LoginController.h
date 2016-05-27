#ifndef LOGINCONTROLLER_H
#define LOGINCONTROLLER_H

#include <QObject>

class LoginController : public QObject
{
    Q_OBJECT
    Q_PROPERTY(bool isLoged READ isLoged NOTIFY isLogedChanged)
public:
    explicit LoginController(QWidget *parent = 0);

    Q_INVOKABLE void login(QString name, QString password);

    bool isLoged();

signals:
    void loginSuccess();
    void loginFailed();
    void isLogedChanged();

public slots:
    void OnLoginSuccess(int id, QByteArray response);
    void OnLoginFailure(int id, QByteArray response);
    void OnUserInfoDone(int id, QByteArray response);
    void OnUserInfoFail(int id, QByteArray response);

private:
    bool _IsLoged;
};

#endif // LOGINCONTROLLER_H
