#include "BodyBugTracker.h"
#include "BodyBugList.h"
#include "BodyBugVisualize.h"

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

    _currentPage = BugTrackerPage::BUGLIST;
    mainLayout->addWidget(_pageManager);
    this->setLayout(mainLayout);
}

void BodyBugTracker::Show(int ID, MainWindow *mainApp)
{
    _id = ID;
    _mainApp = mainApp;
    _currentPage = BugTrackerPage::BUGLIST;
    _pages[_currentPage]->Show(this, NULL);
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
    _pageManager->setCurrentWidget(dynamic_cast<QWidget *>(_pages[page]));
    emit OnPageChanged(page);
}
