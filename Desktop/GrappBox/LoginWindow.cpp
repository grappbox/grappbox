#include <QDebug>
#include "SDataManager.h"
#include "LoginWindow.h"

LoginWindow::LoginWindow(QWidget *parent) : QMainWindow(parent)
{
    _Layout = new QVBoxLayout();
    _GrappboxImage = new QImage(":/Image/Ressources/Title.png");
    _Login = new QLineEdit("Test");
    _Password = new QLineEdit("Password");
    _Password->setEchoMode(QLineEdit::Password);
    _GrappboxLabel = new QLabel();
    _GrappboxLabel->setPixmap(QPixmap::fromImage(*_GrappboxImage));

    _LoginButton = new QPushButton("Login");

    _Layout->addWidget(_GrappboxLabel, 1);
    _Layout->addWidget(_Login, 30);
    _Layout->addWidget(_Password, 30);
    _Layout->addWidget(_LoginButton);

    QWidget *mainWidget = new QWidget();
    mainWidget->setFixedSize(200, 400);
    mainWidget->setLayout(_Layout);

    setFixedSize(200, 400);

    layout()->addWidget(mainWidget);

    connect(_LoginButton, SIGNAL(clicked(bool)), this, SLOT(OnAccept()));
    connect(_Login, SIGNAL(returnPressed()), this, SLOT(OnAccept()));
    connect(_Password, SIGNAL(returnPressed()), this, SLOT(OnAccept()));
}

void LoginWindow::OnAccept()
{
    QVector<QString> data;
    data.push_back(_Login->text());
    data.push_back(_Password->text());
    _RequestId = API::SDataManager::GetCurrentDataConnector()->Post(API::DP_USER_DATA, API::PR_LOGIN, data, this, SLOT(OnLoginSuccess(int, QByteArray)), SLOT(OnLoginFailure(int,QByteArray)));
    this->setDisabled(true);
}

void LoginWindow::OnLoginSuccess(int id, QByteArray response)
{
    QJsonDocument doc;
    doc.fromJson(response);
    QJsonObject obj = doc.object();
    int idUser = obj["user"].toObject()["id"].toInt();
    QString userName = obj["user"].toObject()["firstname"].toString();
    QString userLastName = obj["user"].toObject()["lastname"].toString();
    QString userToken = obj["user"].toObject()["token"].toString();
    API::SDataManager::GetDataManager()->RegisterUserConnected(idUser, userName, userLastName, userToken);
    this->setDisabled(false);
}

void LoginWindow::OnLoginFailure(int id, QByteArray response)
{
    qDebug() << response;
    QMessageBox::critical(this, "Login", "The user or the password is incorrect. Please check the information.");
    this->setDisabled(false);
}
