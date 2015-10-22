#include <QDebug>
#include <QMessageBox>

#include "Body/BodyDashboard.h"
#include "BodyWhiteboard.h"

#include "MainWindow.h"

MainWindow::MainWindow(QWidget *parent)
    : QMainWindow(parent)
{

    // Basic MainWindow Configuration

    setStyleSheet("QMainWindow {background-color: #ffffff;}");
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

    _MainLayout->setSpacing(0);
    _SliderLayout->setSpacing(0);
    _HeaderLayout->setSpacing(0);
    _ContainLayout->setSpacing(0);


    _ProfilWidget = new ProfilMainInformation(0);

    _MenuWidget = new SliderMenu();
    _CurrentCanvas = _StackedLayout->addWidget(new BodyDashboard());
    _MenuWidget->AddMenuItem("Dashboard", _CurrentCanvas);
    _MenuWidget->AddMenuItem("Whiteboard", _StackedLayout->addWidget(new BodyWhiteboard()));
    _CurrentCanvas = 1;

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
    _MenuWidget->ForceChangeMenu(_CurrentCanvas);
    _StackedLayout->setCurrentIndex(_CurrentCanvas);
    dynamic_cast<IBodyContener*>((_StackedLayout->itemAt(_CurrentCanvas)->widget()))->Show(0, NULL);

    QObject::connect(_MenuWidget, SIGNAL(MenuChanged(int)), this, SLOT(OnMenuChange(int)));
}

MainWindow::~MainWindow()
{

}

void MainWindow::OnMenuChange(int id)
{
    qDebug() << "Menu change into " << id;
    QWidget *currentWidget = _StackedLayout->itemAt(_CurrentCanvas)->widget();
    QWidget *nextWidget = _StackedLayout->itemAt(id)->widget();
    if (nextWidget == NULL)
    {
        _MenuWidget->ForceChangeMenu(_CurrentCanvas);
        QMessageBox::information(this, "Not implemented", "Sorry but this functionality is not implemented yet.", "Close");
        return;
    }
    (dynamic_cast<IBodyContener*>(currentWidget))->Hide();
    _CurrentCanvas = id;
    (dynamic_cast<IBodyContener*>(nextWidget))->Show(-1, this);
}
