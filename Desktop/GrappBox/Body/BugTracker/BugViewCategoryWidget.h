#ifndef BUGVIEWCATEGORYWIDGET_H
#define BUGVIEWCATEGORYWIDGET_H

#include "BugTracker/BugCheckableLabel.h"
#include <QWidget>
#include <QStackedWidget>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QLabel>
#include <QCheckBox>
#include <QList>
#include <QJsonObject>
#include <QPushButton>
#include <QLineEdit>

#define ITEM_ID         "id"
#define ITEM_ASSIGNED   "assigned"
#define ITEM_NAME       "name"

class BugViewCategoryWidget : public QWidget
{
    Q_OBJECT

public:
    enum BugCategoryPage
    {
        VIEW,
        ASSIGN
    };

public:
    explicit        BugViewCategoryWidget(QWidget *parent = 0);
    void            DeletePageItems(const BugViewCategoryWidget::BugCategoryPage page);
    void            CreateViewPageItems(const QList<QJsonObject> &items);
    void            CreateAssignPageItems(const QList<QJsonObject> &items);
    BugCategoryPage GetCurrentPage();
    void            DisableAPIAssignation(const bool disable);

signals: //Common signals
    void            OnPageChanged(BugCategoryPage);
    void            OnPageItemsCreated(BugCategoryPage);
    void            OnPageItemsDeleted(BugCategoryPage);

signals: //Assign page slots
    void            OnCreated(int);
    void            OnAssigned(int, QString);
    void            OnDelAssigned(int, QString);

signals: //View page slots

public slots: //Common slots
    void            TriggerOpenPage(const BugCategoryPage page);

public slots: //Assign page slots
    void            TriggerCreateReleased();
    void            TriggerCheckChange(bool checked, int id, QString name);

public slots: //View page slots

private:
    QWidget         *_viewPage;
    QVBoxLayout     *_mainViewLayout;
    QWidget         *_assignPage;
    QVBoxLayout     *_mainAssignLayout;
    QStackedWidget  *_mainWidget;
    QLineEdit       *_creationCategory;
    QPushButton     *_creationBtn;
    bool            _isAPIAssignActivated;
};

#endif // BUGVIEWCATEGORYWIDGET_H
