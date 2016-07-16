#include "RequestView.h"


RequestView::RequestView(RequestDebug *data)
{
    _Data = data;
    bool isRespondOk = data->_ErrorCode != "";
    if (!isRespondOk)
    {
        QObject::connect(&_TimerUpdate, SIGNAL(timeout()), this, SLOT(Update()));
        _TimerUpdate.start(10);
    }

    QFormLayout *formLayout = new QFormLayout();
    formLayout->addRow("Url : ", new QLabel(data->_Url));
    if (!isRespondOk)
    {
        QTime time(0, 0, 0, 0);
        time = time.addMSecs(_Data->_Timer.elapsed());
        _Time = new QLabel(QTime().toString("ss.zzz"));
    }
    else
    {
        QTime time(0, 0, 0, 0);
        time = time.addMSecs(_Data->_Millisecond);
        _Time = new QLabel(time.toString("ss.zzzz"));
    }
    formLayout->addRow("Time : ", _Time);
    QFrame* line2 = new QFrame();
    line2->setFrameShape(QFrame::HLine);
    line2->setFrameShadow(QFrame::Sunken);
    formLayout->addRow(line2);
    QJsonDocument doc = QJsonDocument::fromJson(data->_DataIn);
    QPlainTextEdit *Json = new QPlainTextEdit(doc.toJson(QJsonDocument::Indented));
    formLayout->addRow(Json);
    if (isRespondOk)
    {
        QFrame* line1 = new QFrame();
        line1->setFrameShape(QFrame::HLine);
        line1->setFrameShadow(QFrame::Sunken);
        formLayout->addRow(line1);
        formLayout->addRow("HTTP Return code : ", new QLabel(data->_ErrorCode));
        QByteArray req = data->_DataOut;
        QJsonDocument doc = QJsonDocument::fromJson(req);
        QJsonObject info = doc.object()["info"].toObject();
        formLayout->addRow("API return code : ", new QLabel(info["return_code"].toString()));
        formLayout->addRow("API return message : ", new QLabel(info["return_message"].toString()));
        QPlainTextEdit *Json = new QPlainTextEdit(doc.toJson(QJsonDocument::Indented));
        formLayout->addRow(Json);
    }

    setLayout(formLayout);
}

void RequestView::Update()
{
    QTime time(0, 0, 0, 0);
    time = time.addMSecs(_Data->_Timer.elapsed());
    _Time->setText(time.toString("ss.zzzz"));
}
