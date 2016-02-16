#include "SDataManager.h"
#include "utils.h"
#include "CreateWhiteboardDialog.h"

CreateWhiteboardDialog::CreateWhiteboardDialog(QWidget *parent) : QDialog(parent)
{
	QVBoxLayout *mainLayout = new QVBoxLayout();
	QLabel *title = new QLabel("Enter a name for the new whiteboard");
	_WhiteboardName = new QLineEdit();
	_WhiteboardName->setToolTip("Name of the new whiteboard");
	QPushButton *createButton = new QPushButton("Create");
	createButton->setObjectName("create");
	mainLayout->addWidget(title);
	mainLayout->addWidget(_WhiteboardName);
	mainLayout->addWidget(createButton);

	setLayout(mainLayout);

	QObject::connect(createButton, SIGNAL(clicked(bool)), this, SLOT(OnCreate()));
	QObject::connect(_WhiteboardName, SIGNAL(returnPressed()), this, SLOT(OnCreate()));

	//setWindowFlags(Qt::Popup);
}

CreateWhiteboardDialog::~CreateWhiteboardDialog()
{

}

void CreateWhiteboardDialog::OnCreate()
{
	QVector<QString> data;
	data.push_back(USER_TOKEN);
	data.push_back(TO_STRING(CURRENT_PROJECT));
	data.push_back(_WhiteboardName->text());
	DATA_CONNECTOR->Post(API::DP_WHITEBOARD, API::PR_NEW_WHITEBOARD, data, this, "OnCreateWhiteboardDone", "OnCreateWhiteboardFail");
}

void CreateWhiteboardDialog::OnCreateWhiteboardDone(int, QByteArray data)
{
	emit NewWhiteboard();
	close();
}

void CreateWhiteboardDialog::OnCreateWhiteboardFail(int, QByteArray data)
{
	close();
}