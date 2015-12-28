#ifndef BODYBUGVISUALIZE_H
#define BODYBUGVISUALIZE_H

#include "BugTracker/IBugPage.h"
#include "BugTracker/BugViewTitleWidget.h"
#include "BugTracker/BugViewStatusBar.h"
#include "BugTracker/BugViewPreviewWidget.h"
#include "BugTracker/BugViewCategoryWidget.h"
#include "BugTracker/BugViewAssigneeWidget.h"
#include <QWidget>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QScrollArea>

#define UNUSED __attribute__((unused))

class BodyBugVisualize : public QWidget, public IBugPage
{
    Q_OBJECT
public:
    explicit                BodyBugVisualize(QWidget *parent = 0);
    virtual void            Show(BodyBugTracker *pageManager, QJsonObject *data);
    virtual void            Hide();

signals:
    void                    OnLoadingDone(BodyBugTracker::BugTrackerPage page);

public slots:

private:
    int                     _bugId;
    BodyBugTracker          *_mainApp;
    QVBoxLayout             *_mainLayout;
    QHBoxLayout             *_bodyLayout;
    QVBoxLayout             *_issueLayout;
    QVBoxLayout             *_sideMenuLayout;
    BugViewTitleWidget      *_titleBar;
    BugViewStatusBar        *_statusBar;
    BugViewCategoryWidget   *_categories;
    BugViewAssigneeWidget   *_assignees;
};

#endif // BODYBUGVISUALIZE_H
