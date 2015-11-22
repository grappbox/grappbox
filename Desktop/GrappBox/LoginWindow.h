#ifndef LOGINWINDOW_H
#define LOGINWINDOW_H

#include <QMainWindow>
#include <QImage>
#include <QLineEdit>
#include <QLabel>
#include <QVBoxLayout>
#include <QPicture>
#include <QPushButton>
#include <QMessageBox>
#include <QJsonObject>
#include <QJsonDocument>

class LoginWindow : public QMainWindow
{
    Q_OBJECT
public:
    explicit LoginWindow(QWidget *parent = 0);

signals:

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
