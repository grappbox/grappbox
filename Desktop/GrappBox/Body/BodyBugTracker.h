#ifndef BODYBUGTRACKER_H
#define BODYBUGTRACKER_H

#include "ibodycontener.h"
#include <QWidget>
#include <QStackedWidget>
#include <QMap>
#include <QDebug>

class BodyBugTracker : public QWidget, public IBodyContener
{
    Q_OBJECT

public:
    enum BugTrackerPage
    {
        BUGLIST = 0,
        BUGVIEW,
        BUGCREATE
    };

public:
    explicit                                BodyBugTracker(QWidget *parent = 0);
    virtual void                            Show(int ID, MainWindow *mainApp);
    virtual void                            Hide();

signals:
    void                                    OnLoadingDone(int);
    void                                    OnPageChanged(BugTrackerPage);


public slots:
    void                                    TriggerChangePage(BodyBugTracker::BugTrackerPage newPage, QJsonObject *data);
    void                                    TriggerPageLoaded(BodyBugTracker::BugTrackerPage page);

private:
    int                                     _id;
    MainWindow                              *_mainApp;
    BugTrackerPage                          _currentPage;
    QStackedWidget                          *_pageManager;
    QMap<BugTrackerPage, class IBugPage *>  _pages;
};

#endif // BODYBUGTRACKER_H
