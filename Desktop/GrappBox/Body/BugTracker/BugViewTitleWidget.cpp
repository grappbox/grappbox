#include "BugViewTitleWidget.h"

BugViewTitleWidget::BugViewTitleWidget(QString title, bool creation, QWidget *parent) : QWidget(parent)
{
    QString style;
    _title = new QLineEdit(title);
    _bugID = -1;
    _mainLayout = new QHBoxLayout();
    _creation = creation;
    _btnClose = new QPushButton(tr("Close"));
    _btnEdit = new QPushButton(tr("Edit"));

    _title->setEnabled(creation);
    _mainLayout->addWidget(_title);
    if (!creation)
    {
        _btnClose->setObjectName("Close");    
        _btnEdit->setObjectName("Edit");
        QObject::connect(_btnClose, SIGNAL(released()), this, SLOT(TriggerCloseIssue()));
        QObject::connect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerEditTitle()));
        _mainLayout->addWidget(_btnEdit);
        _mainLayout->addWidget(_btnClose);
    }

    this->setLayout(_mainLayout);

    // [STYLE]
    _title->setPlaceholderText(tr("Enter the issue name here..."));
    style = "QLineEdit{"
            "max-height: 50px;"
            "}"
            "QPushButton#Edit{"
            "background-color: #595959;"
            "color: #ffffff;"
            "border-radius: 2px;"
            "min-width : 75px;"
            "min-height : 40px;"
            "max-width : 75px;"
            "max-height : 40px;"
            "font-size: 15px;"
            "font-weight: bold;"
            "}"
            "QPushButton#Edit:hover{"
            "background-color: #bababa;"
            "}"
            "QPushButton#Close{"
            "background-color: #c0392b;"
            "color: #ffffff;"
            "border-radius: 2px;"
            "min-width : 75px;"
            "min-height : 40px;"
            "max-width : 75px;"
            "max-height : 40px;"
            "font-size: 15px;"
            "font-weight: bold;"
            "}"
            "QPushButton#Close:hover{"
            "background-color: #d36c63;"
            "}";
    this->setStyleSheet(style);
}

void BugViewTitleWidget::TriggerCloseIssue()
{
	BEGIN_REQUEST;
	{
		SET_ON_DONE("TriggerCloseSuccess");
		SET_ON_FAIL("TriggerAPIFailure");
		SET_CALL_OBJECT(this);
		ADD_URL_FIELD(API::SDataManager::GetDataManager()->GetToken());
		ADD_URL_FIELD(_bugID);
		DELETE_REQ(API::DP_BUGTRACKER, API::DR_CLOSE_TICKET_OR_COMMENT);
	}
	END_REQUEST;
}

void BugViewTitleWidget::SetTitle(const QString &title)
{
    _title->setText(title);
}

void BugViewTitleWidget::SetBugID(const int bugId)
{
    _bugID = bugId;
}

void BugViewTitleWidget::TriggerEditTitle()
{
    _title->setEnabled(true);
    _btnEdit->setText(tr("Save"));
    QObject::disconnect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerEditTitle()));
    QObject::connect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerSaveTitle()));
}

void BugViewTitleWidget::TriggerSaveTitle()
{
    _title->setEnabled(false);

    _btnEdit->setText(tr("Edit"));
    QObject::disconnect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerSaveTitle()));
    QObject::connect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerEditTitle()));
    emit OnTitleEdit(_bugID);
}

QString BugViewTitleWidget::GetTitle()
{
    return QString(_title->text());
}

void BugViewTitleWidget::TriggerCloseSuccess(int  id, QByteArray data)
{
    emit OnIssueClosed(_bugID);
}

void BugViewTitleWidget::TriggerAPIFailure(int id, QByteArray data)
{
    QMessageBox::critical(this, tr("Connexion to Grappbox server failed"), tr("We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com"));
}
