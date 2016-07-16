#include "BodyBugTracker.h"
#include "BodyBugList.h"
#include "BodyBugVisualize.h"
#include "BodyBugCreation.h"

BodyBugTracker::BodyBugTracker(QWidget *parent) : QWidget(parent)
{
    QVBoxLayout *mainLayout = new QVBoxLayout();

    _pageManager = new QStackedWidget(this);
    _pageManager->setSizePolicy(QSizePolicy::Expanding,QSizePolicy::Expanding);

    BodyBugList *bugList = new BodyBugList();
    _pageManager->addWidget(bugList);
    _pages[BugTrackerPage::BUGLIST] = bugList;
    QObject::connect(bugList, SIGNAL(OnLoadingDone(BodyBugTracker::BugTrackerPage)), this, SLOT(TriggerPageLoaded(BodyBugTracker::BugTrackerPage)));

    BodyBugVisualize *bugView = new BodyBugVisualize();
    _pageManager->addWidget(bugView);
    _pages[BugTrackerPage::BUGVIEW] = bugView;
    QObject::connect(bugView, SIGNAL(OnLoadingDone(BodyBugTracker::BugTrackerPage)), this, SLOT(TriggerPageLoaded(BodyBugTracker::BugTrackerPage)));

    BodyBugCreation *bugCreate = new BodyBugCreation();
    _pageManager->addWidget(bugCreate);
    _pages[BugTrackerPage::BUGCREATE] = bugCreate;
    QObject::connect(bugCreate, SIGNAL(OnLoadingDone(BodyBugTracker::BugTrackerPage)), this, SLOT(TriggerPageLoaded(BodyBugTracker::BugTrackerPage)));

    _currentPage = BugTrackerPage::BUGLIST;
    mainLayout->addWidget(_pageManager);
    this->setLayout(mainLayout);
}

void BodyBugTracker::Show(int ID, MainWindow *mainApp)
{
    _id = ID;
    _mainApp = mainApp;
    _currentPage = BugTrackerPage::BUGLIST;
    _pages[_currentPage]->Show(this, nullptr);
    emit OnLoadingDone(ID);
}

void BodyBugTracker::Hide()
{
    _pages[_currentPage]->Hide();
    hide();
}

void BodyBugTracker::TriggerChangePage(BodyBugTracker::BugTrackerPage newPage, QJsonObject *data)
{
    _pages[_currentPage]->Hide();
    _pages[newPage]->Show(this, data);
    _currentPage = newPage;
}

void BodyBugTracker::TriggerPageLoaded(BodyBugTracker::BugTrackerPage page)
{
    QWidget *widget = dynamic_cast<QWidget *>(_pages[page]);
    if (!widget)
    {
        QMessageBox::critical(this, tr("Unexpected error"), tr("An unexpected error occured on the bugtracker, please contact us at bug@grappbox.com with the message : 'triggerPageLoaded(Page is not a widget)'"));
        return;
    }
    qDebug() << "Trigger page loaded";
    widget->show();
    _pageManager->setCurrentWidget(widget);
    emit OnPageChanged(page);
}
