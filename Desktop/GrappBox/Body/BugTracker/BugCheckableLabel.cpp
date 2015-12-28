#include "BugCheckableLabel.h"

BugCheckableLabel::BugCheckableLabel(const int id, const QString &name, const bool checked, QWidget *parent) : QWidget(parent)
{
    _mainLayout = new QHBoxLayout();
    _checked = new QCheckBox();
    _name = new QLabel(name);
    _id = id;

    _checked->setChecked(checked);
    _mainLayout->addWidget(_checked);
    _mainLayout->addWidget(_name);
    this->setLayout(_mainLayout);
}

void BugCheckableLabel::TriggerCheckChange(bool checked)
{
    emit OnCheckChanged(checked, _id, _name->text());
}

const int BugCheckableLabel::GetId() const
{
    return _id;
}

const QString &BugCheckableLabel::GetName() const
{
    return _name->text();
}

const bool BugCheckableLabel::IsChecked() const
{
    return _checked->isChecked();
}
