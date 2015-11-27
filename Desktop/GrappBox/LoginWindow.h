#ifndef LOGINWINDOW_H
#define LOGINWINDOW_H

#include <QtWidgets/QMainWindow>
#include <QImage>
#include <QtWidgets/QLineEdit>
#include <QtWidgets/QLabel>
#include <QtWidgets/QVBoxLayout>
#include <QPicture>
#include <QtWidgets/QPushButton>
#include <QtWidgets/QMessageBox>
#include <QJsonObject>
#include <QJsonDocument>

class LoginWindow : public QMainWindow
{
    Q_OBJECT
public:
    explicit LoginWindow(QWidget *parent = 0);

signals:
    void OnLogin();

public slots:
    void OnAccept();

    // API response
    void OnLoginSuccess(int, QByteArray);
    void OnLoginFailure(int, QByteArray);

private:
    QVBoxLayout *_Layout;

    QImage      *_GrappboxImage;
    QLineEdit   *_Login;
    QLineEdit   *_Password;
    QLabel      *_GrappboxLabel;
    QPushButton *_LoginButton;

    int         _RequestId;
};

#endif // LOGINWINDOW_H
