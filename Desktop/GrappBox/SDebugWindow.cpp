#include "SDebugWindow.h"
#include "DataConnectorOnline.h"

static SDebugWindow *__INSTANCE__Debug = nullptr;

SDebugWindow::SDebugWindow()
{
	QFormLayout *mainLayout = new QFormLayout();
	_CurrentApp = new QLabel("Login");
	_InTreatmentRequest = new QListWidget();
	_FinishedRequest = new QListWidget();

	mainLayout->addRow("Current feature launched : ", _CurrentApp);
    mainLayout->addRow(new QLabel("In treatment request"));
    mainLayout->addRow(_InTreatmentRequest);
    mainLayout->addRow(new QLabel("Finished request"));
    mainLayout->addRow(_FinishedRequest);

	setLayout(mainLayout);

    setWindowFlags(Qt::WindowStaysOnTopHint);
    move(pos().x() * -1, pos().y() * -1);

	QObject::connect(_InTreatmentRequest, SIGNAL(itemDoubleClicked(QListWidgetItem*)), this, SLOT(OnRegisterDoubleClick(QListWidgetItem*)));
	QObject::connect(_FinishedRequest, SIGNAL(itemDoubleClicked(QListWidgetItem*)), this, SLOT(OnFinishedDoubleClick(QListWidgetItem*)));
}

SDebugWindow::~SDebugWindow()
{
}

SDebugWindow *SDebugWindow::GetInstance()
{
	if (__INSTANCE__Debug == nullptr)
	{
		__INSTANCE__Debug = new SDebugWindow();
		__INSTANCE__Debug->show();
	}
	return (__INSTANCE__Debug);
}

void SDebugWindow::Destroy()
{
	delete __INSTANCE__Debug;
}

void SDebugWindow::RegisterRequest(int request, QString url, QByteArray dataSent)
{
    RequestDebug *requestData = new RequestDebug();
    requestData->_Timer.start();
    QListWidgetItem *item = new QListWidgetItem(url.remove(URL_API));
	_InTreatmentRequest->addItem(item);
    requestData->_Item = item;
    requestData->_Url = url;
    requestData->_DataIn = dataSent;
    requestData->_ErrorCode = "";
    _MapRequest[request] = requestData;
}

void SDebugWindow::ReturnRequest(int request, QString errorCode, QString errorMessage, QByteArray dataReceive)
{
    if (!_MapRequest.contains(request))
        return;
    _InTreatmentRequest->takeItem(_InTreatmentRequest->row(_MapRequest[request]->_Item));
    _MapRequest[request]->_ErrorCode = errorCode;
    _MapRequest[request]->_ErrorMessage = (errorMessage == "Unknown error") ? "" : errorMessage;
    _MapRequest[request]->_DataOut = dataReceive;
    _MapRequest[request]->_Millisecond = _MapRequest[request]->_Timer.elapsed();
    QListWidgetItem *item = new QListWidgetItem(_MapRequest[request]->_Url.remove(URL_API));
    if (errorMessage != QString("Unknown error"))
		item->setTextColor(QColor(255, 0, 0));
	_FinishedRequest->addItem(item);
    _MapRequest[request]->_Item = item;
}

void SDebugWindow::SetCurrentApplication(QString appName)
{
	_CurrentApp->setText(appName);
}

void SDebugWindow::OnRegisterDoubleClick(QListWidgetItem *item)
{
    for (QMap<int, RequestDebug*>::iterator it = _MapRequest.begin(); it != _MapRequest.end(); ++it)
	{
        if (it.value()->_Item == item)
        {
            RequestView *rv = new RequestView(it.value());
            rv->show();
            return;
        }
    }
}

void SDebugWindow::OnFinishedDoubleClick(QListWidgetItem *item)
{
    for (QMap<int, RequestDebug*>::iterator it = _MapRequest.begin(); it != _MapRequest.end(); ++it)
	{
        if (it.value()->_Item == item)
        {
            RequestView *rv = new RequestView(it.value());
            rv->show();
            return;
        }
	}
}
