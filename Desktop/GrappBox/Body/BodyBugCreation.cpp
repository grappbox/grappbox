#include "BodyBugCreation.h"

BodyBugCreation::BodyBugCreation(QWidget *parent) : QWidget(parent)
{
    QString style;
    QWidget *widgetTitleCategory = new QWidget();
    QWidget *widgetTitleAssignee = new QWidget();
    QLabel *lblTitleCategory = new QLabel(tr("Categories"));
    QLabel *lblTitleAssignee = new QLabel(tr("Assignee"));
    QHBoxLayout *layoutTitleCategory = new QHBoxLayout();
    QHBoxLayout *layoutTitleAssignee = new QHBoxLayout();
    QVBoxLayout *layoutCategoryArea = new QVBoxLayout();
    QVBoxLayout *layoutAssigneeArea = new QVBoxLayout();
    _btnCategoriesAssign = new QPushButton(PH_ASSIGNATION);
    _btnAssigneeAssign = new QPushButton(PH_ASSIGNATION);
    _bugId = -1;
    _mainLayout = new QVBoxLayout();
    _bodyLayout = new QHBoxLayout();
    _issueLayout = new QVBoxLayout();
    _sideMenuLayout = new QVBoxLayout();
    _titleBar = new BugViewTitleWidget(tr("Enter the issue name here..."), true);
    _categories = new BugViewCategoryWidget();
    _assignees = new BugViewAssigneeWidget();
    _commentArea = new QScrollArea();
    _commentLayout = new QVBoxLayout();
    _categoriesArea = new QScrollArea();
    _assigneesArea = new QScrollArea();

    _issueLayout->addWidget(_commentArea);

    widgetTitleCategory->setLayout(layoutTitleCategory);
    layoutTitleCategory->addWidget(lblTitleCategory);
    layoutTitleCategory->addWidget(_btnCategoriesAssign);

    widgetTitleAssignee->setLayout(layoutTitleAssignee);
    layoutTitleAssignee->addWidget(lblTitleAssignee);
    layoutTitleAssignee->addWidget(_btnAssigneeAssign);

    _categoriesArea->setMaximumWidth(250);
    _assigneesArea->setMaximumWidth(250);

    layoutAssigneeArea->addWidget(_assignees);
    layoutCategoryArea->addWidget(_categories);

    _categoriesArea->setLayout(layoutCategoryArea);
    _assigneesArea->setLayout(layoutAssigneeArea);

    _sideMenuLayout->addWidget(widgetTitleCategory);
    _sideMenuLayout->addWidget(_categoriesArea);
    _sideMenuLayout->addWidget(widgetTitleAssignee);
    _sideMenuLayout->addWidget(_assigneesArea);

    _commentArea->setLayout(_commentLayout);

    _bodyLayout->addLayout(_issueLayout);
    _bodyLayout->addLayout(_sideMenuLayout);

    QObject::connect(_btnAssigneeAssign, SIGNAL(released()), this, SLOT(TriggerAssigneeBtnReleased()));
    QObject::connect(_btnCategoriesAssign, SIGNAL(released()), this, SLOT(TriggerCategoryBtnReleased()));


    _mainLayout->addWidget(_titleBar);
    _mainLayout->addLayout(_bodyLayout);
    this->setLayout(_mainLayout);

    _btnCategoriesAssign->hide();
    _btnAssigneeAssign->hide();
    _btnCategoriesAssign->setEnabled(false);
    _btnAssigneeAssign->setEnabled(false);
    _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::ASSIGN);
    _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
    _categories->DisableAPIAssignation(true);
    _assignees->DisableAPIAssignation(true);

    //Design
    style = "QPushButton{"
            "background-color: #595959;"
            "color : #ffffff;"
            "border-radius: 2px;"
            "min-width : 80px;"
            "min-height : 25px;"
            "max-width : 80px;"
            "max-height : 25px;"
            "font-size: 12px;"
            "font-weight: bold;"
            "}"
            "QPushButton:hover{"
            "background-color: #bababa;"
            "}"
            "QScrollArea{"
            "border: 0px;"
            "background-color: #FFFFFF;"
            "}"
            "QWidget#Title{"
            "border-bottom: 1px solid #000000;"
            "}"
            "QVBoxLayout#Issues{"
            "alternate-background-color: #a6a6a6;"
            "background: #d9d9d9;"
            "}";
    this->setStyleSheet(style);
}

void BodyBugCreation::Show(BodyBugTracker *pageManager, QJsonObject *data)
{
    _bugId = -1;
    _mainApp = pageManager;
    _titleBar->SetBugID(_bugId);
    _titleBar->SetTitle(tr("Enter the issue name here..."));
    this->DeleteComments();
    //TODO : Link API
    QList<QJsonObject> fdataAssigneeView, fdataAssigneeAssign, fdataCategoryView, fdataCategoryAssign;
    QJsonObject obj3, obj5, obj6, obj7;

    obj5.insert("id", 4);
    obj5.insert("assigned", false);
    obj5.insert("name", "hemmer_r");
    obj6.insert("id", 3);
    obj6.insert("assigned", false);
    obj6.insert("name", "feytou_p");

    obj3.insert("id", 1);
    obj3.insert("assigned", false);
    obj3.insert("name", "question");
    obj7.insert("id", 2);
    obj7.insert("assigned", false);
    obj7.insert("name", "help wanted");

    fdataAssigneeAssign.append(obj5);
    fdataAssigneeAssign.append(obj6);
    fdataCategoryAssign.append(obj3);
    fdataCategoryAssign.append(obj7);
    //End Fake Data
    _assignees->CreateAssignPageItems(fdataAssigneeAssign);
    _assignees->CreateViewPageItems(fdataAssigneeView);
    _categories->CreateAssignPageItems(fdataCategoryAssign);
    _categories->CreateViewPageItems(fdataCategoryView);
    emit OnLoadingDone(BodyBugTracker::BUGCREATE);
}

void BodyBugCreation::Hide()
{
    _assignees->DeletePageItems(BugViewAssigneeWidget::BugAssigneePage::VIEW);
    _assignees->DeletePageItems(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
    _categories->DeletePageItems(BugViewCategoryWidget::BugCategoryPage::VIEW);
    _categories->DeletePageItems(BugViewCategoryWidget::BugCategoryPage::VIEW);
    hide();
}

void BodyBugCreation::TriggerAssigneeBtnReleased()
{
    if (_assignees->GetCurrentPage() == BugViewAssigneeWidget::BugAssigneePage::VIEW)
    {
        _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
        _btnAssigneeAssign->setText(PH_BACK);
    }
    else
    {
        _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::VIEW);
        _btnAssigneeAssign->setText(PH_ASSIGNATION);
    }
}

void BodyBugCreation::TriggerCategoryBtnReleased()
{
    if (_categories->GetCurrentPage() == BugViewCategoryWidget::BugCategoryPage::VIEW)
    {
        _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::ASSIGN);
        _btnCategoriesAssign->setText(PH_BACK);
    }
    else
    {
        _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::VIEW);
        _btnCategoriesAssign->setText(PH_ASSIGNATION);
    }
}

void BodyBugCreation::DeleteComments()
{
    QLayoutItem *currentItem;
    BugViewPreviewWidget *commentWidget = new BugViewPreviewWidget(true, true);

    while ((currentItem = _commentLayout->itemAt(0)) != NULL)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(NULL);
        _commentLayout->removeItem(currentItem);
    }
    commentWidget->setFixedHeight(COMMENTBOX_HEIGHT);
    _commentLayout->addWidget(commentWidget);
    _commentLayout->setAlignment(commentWidget, Qt::AlignTop);
    _commentWidget = commentWidget;
    QObject::connect(_commentWidget, SIGNAL(OnCommented()), this, SLOT(TriggerComment()));
}

void BodyBugCreation::TriggerComment()
{
    //TODO : Link API save new issue
    QVector<QString> data;
    data.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(_titleBar->GetTitle());
    data.append(_commentWidget->GetComment());
    data.append(QString::number(BUGSTATE_OPEN));
    data.append("");

    API::SDataManager::GetCurrentDataConnector()->Post(API::DP_BUGTRACKER, API::PR_CREATE_BUG, data, this, "TriggerBugCreated", "TriggerAPIFailure");
}

void BodyBugCreation::TriggerBugCreated(int UNUSED id, QByteArray data)
{
     //TODO: post comment and assignee and categories
    QVector<QString> commentData;
    QJsonObject json = QJsonDocument::fromBinaryData(data).object();

    commentData.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    commentData.append(API::SDataManager::GetDataManager()->GetToken());
    commentData.append(_commentWidget->GetCommentTitle());
    commentData.append(_commentWidget->GetComment());
    commentData.append(QString::number(json["ticket"].toObject()["id"].toInt()));

    API::SDataManager::GetCurrentDataConnector()->Post(API::DP_BUGTRACKER, API::PR_COMMENT_BUG, commentData, this, "TriggerBugCommented", "TriggerAPIFailure");
    QJsonObject *intent = new QJsonObject();

    intent->insert("id", -1); //TODO : put bug API ID
    intent->insert("title", this->_titleBar->GetTitle());
    _mainApp->TriggerChangePage(BodyBugTracker::BugTrackerPage::BUGVIEW, intent);
}

void BodyBugCreation::TriggerBugCommented(int UNUSED id, QByteArray data)
{

}

void BodyBugCreation::TriggerAPIFailure(int UNUSED id, QByteArray UNUSED data)
{
    QMessageBox::critical(this, tr("Connexion to Grappbox server failed"), tr("We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com"));
}
