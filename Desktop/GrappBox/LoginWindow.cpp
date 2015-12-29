#include <QDebug>
#include <QFile>
#include "SFontLoader.h"
#include "SDataManager.h"
#include "LoginWindow.h"

LoginWindow::LoginWindow(QWidget *mainP, QWidget *parent) : QMainWindow(parent)
{
    QFile file(":/Configuration/Ressources/ConfigurationFiles/Base.qss");
    file.open(QFile::ReadOnly);
    QString styleSheet = QLatin1String(file.readAll());
    setObjectName("Login");
    setStyleSheet(styleSheet);
    _Layout = new QVBoxLayout();
    _GrappboxImage = new QImage(":/Image/Ressources/Title.png");
    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPixelSize(18);
    _Login = new QLineEdit("leo.nadeau@epitech.eu");
    _Login->setFixedHeight(30);
    _Login->setAlignment(Qt::AlignCenter);
    _Login->setFont(font);
    _Login->setFixedWidth(240);
    _Password = new QLineEdit("nadeau_l");
    _Password->setEchoMode(QLineEdit::Password);
    _Password->setFixedHeight(30);
    _Password->setAlignment(Qt::AlignCenter);
    _Password->setFont(font);
    _Password->setFixedWidth(240);

    _GrappboxLabel = new QLabel();
    _GrappboxLabel->setAlignment(Qt::AlignCenter);
    _GrappboxLabel->setPixmap(QPixmap::fromImage(*_GrappboxImage));

    _LoginButton = new QPushButton("Login");
    _LoginButton->setFixedHeight(36);
    _LoginButton->setStyleSheet("QPushButton { "
                                "background: #d9d9d9;"
                                "color: #FFFFFF; "
                                "border-style:none; "
                                "border-bottom-style: solid; "
                                "border-width: 1px; "
                                "border-color: #FFFFFF;}"
                                "QPushButton:hover {background: #a6a6a6;}"
                                "QPushButton:pressed {background: #c0392b;}");

    _Layout->addWidget(_GrappboxLabel);
    _Layout->addWidget(_Login);
    _Layout->addWidget(_Password);
    _Layout->addSpacing(5);
    _Layout->addWidget(_LoginButton);
    _Layout->setContentsMargins(0, 0, 0, 0);
    _Layout->setAlignment(_Login, Qt::AlignHCenter);
    _Layout->setAlignment(_Password, Qt::AlignHCenter);

    QWidget *mainWidget = new QWidget();
    mainWidget->setFixedSize(250, 300);
    mainWidget->setLayout(_Layout);

    setFixedSize(250, 300);

    layout()->addWidget(mainWidget);

    connect(_LoginButton, SIGNAL(clicked(bool)), this, SLOT(OnAccept()));
    connect(_Login, SIGNAL(returnPressed()), this, SLOT(OnAccept()));
    connect(_Password, SIGNAL(returnPressed()), this, SLOT(OnAccept()));

    connect(this, SIGNAL(OnLogin()), mainP, SLOT(OnLogin()));
}

void LoginWindow::OnAccept()
{
    QVector<QString> data;
    data.push_back(_Login->text());
    data.push_back(_Password->text());
    qDebug() << " On accept !";
    _RequestId = API::SDataManager::GetCurrentDataConnector()->Post(API::DP_USER_DATA, API::PR_LOGIN, data, this, "OnLoginSuccess", "OnLoginFailure");
    this->setDisabled(true);
}

void LoginWindow::OnLoginSuccess(int id, QByteArray response)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(response);
    QJsonObject obj = doc.object();
    int idUser = obj["user"].toObject()["id"].toInt();
    QString userName = obj["user"].toObject()["firstname"].toString();
    QString userLastName = obj["user"].toObject()["lastname"].toString();
    QString userToken = obj["user"].toObject()["token"].toString();
    API::SDataManager::GetDataManager()->RegisterUserConnected(idUser, userName, userLastName, userToken);
    this->setDisabled(false);
    emit OnLogin();
}

void LoginWindow::OnLoginFailure(int id, QByteArray response)
{
    qDebug() << response;
    QMessageBox::critical(this, "Login", "The user or the password is incorrect. Please check the information.");
    this->setDisabled(false);
}
