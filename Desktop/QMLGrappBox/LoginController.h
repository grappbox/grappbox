#ifndef LOGINCONTROLLER_H
#define LOGINCONTROLLER_H

#include <QObject>

class LoginController : public QObject
{
    Q_OBJECT
public:
    explicit LoginController(QWidget *parent = 0);

    Q_INVOKABLE void login(QString name, QString password);

signals:
    void loginSuccess();
    void loginFailed();

public slots:
    void OnLoginSuccess(int id, QByteArray response);
    void OnLoginFailure(int id, QByteArray response);
};

#endif // LOGINCONTROLLER_H
