#include "BodyWhiteboardWritingText.h"

BodyWhiteboardWritingText::BodyWhiteboardWritingText(QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QVBoxLayout();
    _ButtonLayout = new QHBoxLayout();
    _OptionLayout = new QHBoxLayout();
    _Text = new QTextEdit();
    _Text->setFixedHeight(60);
    _Italic = new QCheckBox();
    _Bold = new QCheckBox();
    _Size = new QSpinBox();
    _Size->setMinimum(8);
    _Size->setMaximum(128);
    _Size->setValue(20);
    _Cancel = new QPushButton("Cancel");
    _Accept = new QPushButton("Add text");

    _OptionLayout->addWidget(_Size);
    _OptionLayout->addWidget(_Italic);
    _OptionLayout->addWidget(_Bold);

    _ButtonLayout->addWidget(_Cancel);
    _ButtonLayout->addWidget(_Accept);

    _MainLayout->addLayout(_OptionLayout);
    _MainLayout->addWidget(_Text);
    _MainLayout->addLayout(_ButtonLayout);

    setLayout(_MainLayout);
    setWindowFlags(Qt::Window | Qt::FramelessWindowHint | Qt::WindowStaysOnTopHint);

    this->setFixedHeight(150);

    connect(_Accept, SIGNAL(clicked(bool)), this, SLOT(OnAcceptPush()));
    connect(_Cancel, SIGNAL(clicked(bool)), this, SLOT(OnCancelPush()));
}

void BodyWhiteboardWritingText::OnAcceptPush()
{
    if (_Text->toPlainText() != "")
        emit Accept(_Text->toPlainText(), _Italic->checkState() == Qt::Checked, _Bold->checkState() == Qt::Checked, _Size->value());
    else
        emit Cancel();
}

void BodyWhiteboardWritingText::OnCancelPush()
{
    emit Cancel();
}

