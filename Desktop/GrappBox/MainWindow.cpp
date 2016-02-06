#include <QDebug>
#include <QtWidgets/QMessageBox>
#include <QMovie>
#include "SDataManager.h"

#include "Body/BodyCalendar.h"
#include "Body/BodyDashboard.h"
#include "BodyWhiteboard.h"
#include "Body/BodyUserSettings.h"
#include "Body/BodyProjectSettings.h"
#include "Body/BodyBugTracker.h"
#include "Body/BodyTimeline.h"

#include "MainWindow.h"

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent)
{

    // Basic MainWindow Configuration
    QFile file(":/Configuration/Ressources/ConfigurationFiles/Base.css");
    file.open(QFile::ReadOnly);
    QString styleSheet = QLatin1String(file.readAll());
    setStyleSheet(styleSheet);
    this->layout()->setSpacing(0);
    this->layout()->setMargin(0);
    this->setWindowTitle("Grappbox");
    this->setMinimumSize(1440, 900);
    this->setContentsMargins(0, 0, 0, 0);

    // Widget creation

    QWidget *mainWidget = new QWidget();

    _MainLayout = new QVBoxLayout();
    _SliderLayout = new QVBoxLayout();
    _HeaderLayout = new QHBoxLayout();
    _ContainLayout = new QHBoxLayout();

    _StackedLayout = new QStackedLayout();

    _MainLayout->setContentsMargins(0, 0, 0, 0);
    _SliderLayout->setContentsMargins(0, 0, 0, 0);
    _HeaderLayout->setContentsMargins(0, 0, 0, 0);
    _ContainLayout->setContentsMargins(0, 0, 0, 0);
    _MainLayout->setSpacing(0);
    _SliderLayout->setSpacing(0);
    _HeaderLayout->setSpacing(0);
    _ContainLayout->setSpacing(0);


    _ProfilWidget = new ProfilMainInformation(this);

    _MenuWidget = new SliderMenu();

    _LoadingImage = new QLabel(this);
    _LoadingImage->setAlignment(Qt::AlignCenter);
    QMovie *loading = new QMovie(":/icon/Ressources/Icon/Loading.gif");
    _LoadingImage->setMovie(loading);
    loading->start();
    _LoadingId = _StackedLayout->addWidget(_LoadingImage);

    BodyDashboard *dashboard = new BodyDashboard();
    BodyUserSettings *userSettings = new BodyUserSettings();
    BodyProjectSettings *projectSettings = new BodyProjectSettings();
    BodyTimeline *timeline = new BodyTimeline();
	BodyCalendar *calendar = new BodyCalendar();

    connect(dashboard, SIGNAL(OnLoadingDone(int)), this, SLOT(OnLoadingFinished(int)));
    connect(userSettings, SIGNAL(OnLoadingDone(int)), this, SLOT(OnLoadingFinished(int)));
    connect(projectSettings, SIGNAL(OnLoadingDone(int)), this, SLOT(OnLoadingFinished(int)));
    connect(timeline, SIGNAL(OnLoadingDone(int)), this, SLOT(OnLoadingFinished(int)));
	connect(calendar, SIGNAL(OnLoadingDone(int)), this, SLOT(OnLoadingFinished(int)));

    _CurrentCanvas = _StackedLayout->addWidget(dashboard);
    _MainPageId = _CurrentCanvas;
    _MenuWidget->AddMenuItem("Dashboard", _CurrentCanvas, false, false);
	_MenuWidget->AddMenuItem("Calendar", _StackedLayout->addWidget(calendar), false, false);
    BodyWhiteboard *whiteboard = new BodyWhiteboard();
    connect(whiteboard, SIGNAL(OnLoadingDone(int)), this, SLOT(OnLoadingFinished(int)));
    _MenuWidget->AddMenuItem("Whiteboard", _StackedLayout->addWidget(whiteboard));
    _MenuWidget->AddMenuItem("Timeline", _StackedLayout->addWidget(timeline));

    BodyBugTracker *bugTracker = new BodyBugTracker();
    connect(bugTracker, SIGNAL(OnLoadingDone(int)), this, SLOT(OnLoadingFinished(int)));
    _MenuWidget->AddMenuItem("BugTracker", _StackedLayout->addWidget(bugTracker));

    // Here change the body for settings
    _UserSettingsId = _StackedLayout->addWidget(userSettings);
    _SettingsId = _StackedLayout->addWidget(projectSettings);

    _MenuWidget->AddMenuItem("", _UserSettingsId, true);
    _MenuWidget->AddMenuItem("", _SettingsId, true);

    _GrabboxNameLabel = new QLabel();
    _GrabboxNameLabel->setPixmap(QPixmap(":/Image/Ressources/Title.png"));
    _GrabboxNameLabel->setStyleSheet("padding-left: 10px;");
    _NotificationButton = new QPushButton();
    _AlertButton = new QPushButton();
    _ParameterButton = new QPushButton();

    _NotificationButton->setFixedSize(50, 42);
    _AlertButton->setFixedSize(50, 42);
    _ParameterButton->setFixedSize(50, 42);

    _NotificationButton->setStyleSheet("QPushButton {background-color: #2d2f31;"
                                 "background-image: url(:/icon/Ressources/Icon/Message.png);"
                                 "background-repeat: no-repeat;"
                                 "border:none;"
                                 "background-position: center center;}"
                                 "QPushButton:hover {background-color: #3a3d40;}"
                                 "QPushButton:pressed {background-color: #ffffff;}");

    _AlertButton->setStyleSheet("QPushButton {background-color: #2d2f31;"
                                 "background-image: url(:/icon/Ressources/Icon/Notification.png);"
                                 "background-repeat: no-repeat;"
                                "border:none;"
                                 "background-position: center center;}"
                                 "QPushButton:hover {background-color: #3a3d40;}"
                                 "QPushButton:pressed {background-color: #ffffff;}");

    _ParameterButton->setStyleSheet("QPushButton {background-color: #2d2f31;"
                                 "background-image: url(:/icon/Ressources/Icon/Settings.png);"
                                 "background-repeat: no-repeat;"
                                    "border:none;"
                                 "background-position: center center;}"
                                 "QPushButton:hover {background-color: #3a3d40;}"
                                 "QPushButton:pressed {background-color: #ffffff;}");

    _SliderLayout->addWidget(_ProfilWidget);
    _SliderLayout->addWidget(_MenuWidget);

    QWidget *widgetHeader = new QWidget();
    widgetHeader->setStyleSheet("background: #2d2f31;");
    widgetHeader->setLayout(_HeaderLayout);
    _HeaderLayout->addWidget(_GrabboxNameLabel, 40);
    _HeaderLayout->addWidget(_NotificationButton, 1);
    _HeaderLayout->addWidget(_AlertButton, 1);
    _HeaderLayout->addWidget(_ParameterButton, 1);
    _HeaderLayout->setSpacing(0);
    _HeaderLayout->setMargin(0);

    // Layout disposition

    _MainLayout->addWidget(widgetHeader);
    _MainLayout->addLayout(_ContainLayout);
    QWidget *wi = new QWidget();
    wi->setLayout(_SliderLayout);
    wi->setFixedWidth(285);
    _ContainLayout->addWidget(wi);
    _ContainLayout->addLayout(_StackedLayout);
    _ContainLayout->setSpacing(0);
    _ContainLayout->setMargin(0);

    mainWidget->setLayout(_MainLayout);
    this->setCentralWidget(mainWidget);

    _Login = new LoginWindow(this);

    QObject::connect(_MenuWidget, SIGNAL(MenuChanged(int)), this, SLOT(OnMenuChange(int)));
    QObject::connect(_MenuWidget, SIGNAL(ProjectChange()), this, SLOT(OnProjectChange()));

    _Login->show();
    this->hide();

    connect(_ProfilWidget, SIGNAL(OnUserSettings()), this, SLOT(OnUserSettings()));
    connect(_ProfilWidget, SIGNAL(OnMainSettings()), this, SLOT(OnSettings()));
}

MainWindow::~MainWindow()
{

}

void MainWindow::OnLogin()
{
    _Login->hide();
    this->show();
    _MenuWidget->ForceChangeMenu(_CurrentCanvas);
    _MenuWidget->UpdateProject();
    OnMenuChange(_CurrentCanvas);
}

void MainWindow::OnLogout()
{
    QVector<QString> data;
    data.push_back(API::SDataManager::GetDataManager()->GetToken());
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_USER_DATA, API::GR_LOGOUT, data, nullptr, nullptr, nullptr);
    API::SDataManager::GetDataManager()->LogoutUser();
    _Login->show();
    hide();
}

void MainWindow::OnProjectChange()
{
    int currentProject = API::SDataManager::GetDataManager()->GetCurrentProject();
    if (currentProject == -1)
    {
        OnMenuChange(_MainPageId);
        return;
    }
    QWidget *currentWidget = _StackedLayout->itemAt(_CurrentCanvas)->widget();
    (dynamic_cast<IBodyContener*>(currentWidget))->Hide();
    _StackedLayout->setCurrentIndex(0);
    (dynamic_cast<IBodyContener*>(currentWidget))->Show(_CurrentCanvas, this);
}

void MainWindow::OnSettings()
{
    _CurrentCanvas = _SettingsId;
    _MenuWidget->ForceChangeMenu(_CurrentCanvas);
    OnMenuChange(_CurrentCanvas);
}

void MainWindow::OnUserSettings()
{
    _CurrentCanvas = _UserSettingsId;
    _MenuWidget->ForceChangeMenu(_CurrentCanvas);
    OnMenuChange(_CurrentCanvas);
}

void MainWindow::OnMenuChange(int id)
{
    QWidget *currentWidget = _StackedLayout->itemAt(_CurrentCanvas)->widget();
    QWidget *nextWidget = _StackedLayout->itemAt(id)->widget();
    if (nextWidget == nullptr)
    {
        _MenuWidget->ForceChangeMenu(_CurrentCanvas);
        QMessageBox::information(this, "Not implemented", "Sorry but this functionality is not implemented yet.", "Close");
        return;
    }
    (dynamic_cast<IBodyContener*>(currentWidget))->Hide();
    _CurrentCanvas = id;
    _StackedLayout->setCurrentIndex(0);
    (dynamic_cast<IBodyContener*>(nextWidget))->Show(_CurrentCanvas, this);
    nextWidget->updateGeometry();
}

void MainWindow::OnLoadingFinished(int canvas)
{
	qDebug() << canvas << " : " << _CurrentCanvas;
    if (canvas == _CurrentCanvas)
    {
        _StackedLayout->setCurrentIndex(_CurrentCanvas);
        _StackedLayout->itemAt(canvas)->widget()->show();
    }
}
